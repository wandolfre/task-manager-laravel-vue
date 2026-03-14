<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the User Eloquent model.
 *
 * Verifies model attributes, password hashing, hidden fields,
 * and the HasApiTokens trait integration.
 */
class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the password is automatically hashed when set.
     *
     * The User model casts 'password' to 'hashed', so raw passwords
     * should never be stored in the database.
     */
    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plaintext123',
        ]);

        // The stored password should NOT be the plaintext value
        $this->assertNotEquals('plaintext123', $user->password);

        // But it should verify correctly via Hash::check
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('plaintext123', $user->password));
    }

    /**
     * Test that password and remember_token are hidden from serialization.
     *
     * When the model is converted to JSON (e.g., in API responses),
     * sensitive fields must be excluded.
     */
    public function test_sensitive_fields_are_hidden(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    /**
     * Test that the user's fillable attributes include name, last_name, email, and password.
     */
    public function test_fillable_attributes(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('last_name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    /**
     * Test that the user can create Sanctum API tokens.
     *
     * Verifies the HasApiTokens trait is functional.
     */
    public function test_user_can_create_api_tokens(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $this->assertNotEmpty($token->plainTextToken);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'test-token',
        ]);
    }
}
