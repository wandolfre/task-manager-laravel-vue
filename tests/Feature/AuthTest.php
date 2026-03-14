<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the authentication API endpoints (register, login, logout).
 *
 * Covers both success and validation/failure scenarios to ensure
 * robust authentication handling.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a new user can register successfully with valid data.
     *
     * Expects HTTP 201 with user data and a Sanctum API token.
     */
    public function test_register_success(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'last_name', 'email'],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    /**
     * Test that registration fails when required fields are missing or invalid.
     *
     * Expects HTTP 422 with validation error messages.
     */
    public function test_register_validation_fails(): void
    {
        // Missing all required fields
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'last_name', 'email', 'password']);
    }

    /**
     * Test that registration fails when password confirmation does not match.
     *
     * Expects HTTP 422 with a password validation error.
     */
    public function test_register_password_confirmation_mismatch(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongconfirm',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test that an existing user can log in with valid credentials.
     *
     * Expects HTTP 200 with user data and a Sanctum API token.
     */
    public function test_login_success(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'last_name', 'email'],
                'token',
            ]);
    }

    /**
     * Test that login fails with invalid credentials.
     *
     * Expects HTTP 401 with a generic error message (no user enumeration).
     */
    public function test_login_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'The provided credentials are incorrect.']);
    }

    /**
     * Test that an authenticated user can log out and their token is revoked.
     *
     * Expects HTTP 200 and the token should no longer work for subsequent requests.
     */
    public function test_logout_success(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Successfully logged out.']);

        // Verify the token was deleted from the database
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
