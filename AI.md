# AI Usage Disclosure

This project was developed using AI-assisted tooling to accelerate the development workflow. Below is a transparent summary of how AI was incorporated into the process.

## Tools Used

- **Claude (Anthropic)** — Used as a pair-programming assistant within the IDE for code generation, debugging, and boilerplate scaffolding.
- **Stitch (Google)** — Used to rapidly prototype UI wireframes (HTML/CSS mockups) that served as the visual reference for the Vue.js frontend.

## Architect Responsibilities

### Architecture & Technical Decisions
- Defined the full-stack architecture: Laravel 12 REST API + Vue.js 3 SPA with Sanctum token authentication.
- Chose the tech stack, database schema design, authentication strategy (API tokens over sessions), and state management approach (Pinia).
- Designed the API contract (endpoints, filtering, sorting, pagination) and ownership-based authorization model.

### UI/UX Design
- Created 6 screen wireframes in Stitch defining the visual language: dark theme, component layout, interaction patterns (modals, toggles, cards).
- Directed the design system: color palette, typography, iconography, and responsive breakpoints.

### Quality Assurance & Code Review
- Reviewed all generated code for correctness, security (SQL injection prevention, ownership checks), and adherence to Laravel/Vue best practices.
- Defined the test strategy covering unit tests, integration tests, authentication, authorization, and validation.
- Verified all 42 tests pass (117 assertions) and the production build compiles cleanly.

### Project Management
- Managed the git workflow, branch strategy, and commit history.
- Wrote the project documentation (README.md) and this disclosure.

## How AI Assisted

- **Code scaffolding** — Generated boilerplate for migrations, models, controllers, and Vue components based on my specifications.
- **Pair programming** — Assisted with implementation details like Axios interceptors, Pinia store patterns, and Tailwind utility classes.
- **Test writing** — Helped scaffold PHPUnit test cases based on the test plan I defined.
- **Debugging** — Assisted in diagnosing issues like Sanctum token caching in tests and phpunit.xml configuration conflicts.

## Why This Approach

Using AI as a development accelerator is a standard practice in modern engineering teams. It allows developers to focus on architecture, design decisions, and quality control while reducing time spent on repetitive implementation tasks. Every line of code in this project was reviewed and understood before being committed.
