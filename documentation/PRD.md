# Project Requirements Document: Laravel Markdown Emails

## 1. Introduction
This document outlines the requirements for a Laravel feature that generates emails using Markdown with enhanced button functionality.

## 2. Goals
*   Enable the use of Markdown for email content creation.
*   Provide a consistent email design through a base template.
*   Allow for dynamic content within emails.
*   **Support styled buttons with primary and secondary variants.**

## 3. Core Functionality
*   Generate emails using Markdown syntax.
*   Utilize a base HTML template for consistent email structure.
*   Support dynamic content injection into the base template.
*   **Generate styled buttons with configurable types and styling.**

## 4. Base Template Requirements
*   Dynamic logo display.
*   Dynamic business name display.
*   Section for links.
*   Mobile-responsive design.
*   **Support for styled button rendering.**

## 5. Button System Requirements
*   **Primary buttons for main actions (e.g., "Get Started", "Login").**
*   **Secondary buttons for supplementary actions (e.g., "Learn More", "Contact Us").**
*   **Additional button types: success, danger, warning.**
*   **Configurable button styling via configuration file.**
*   **XSS protection for button text and URLs.**
*   **Email-client compatible inline CSS styling.**

## 6. Error Handling and Logging
*   Detailed error logging for email generation and sending.
*   Graceful handling of Markdown parsing errors.
*   **Validation of button parameters and fallback to default styling.**

## 7. Security Considerations
*   Protection against email header injection.
*   Sanitization of user-generated content within Markdown.
*   **HTML escaping for button text and URLs to prevent XSS attacks.**
*   **URL validation to block dangerous protocols (javascript:, data:, vbscript:, file:, about:).**
*   **CSS value sanitization to prevent CSS injection attacks.**
*   **DoS protection through length limits on CSS values.**
*   **Whitelist-based URL validation for safe protocols only.**

## 8. Development Guidelines
*   Adhere to Laravel best practices and coding standards.
*   Minimum Laravel version: 10.x.
*   **Follow email client compatibility standards for button styling.**

## 9. Package Structure and Testing
*   Develop as a reusable Laravel package.
*   Include unit and integration tests.
*   **Comprehensive test coverage for button functionality.**

## 10. Database Storage
*   Store generated emails in a PostgreSQL database.
*   Example schema: (Include basic example here)

## 11. Events
*   Fire events to retrieve emails.

## 12. Logo
*   Logo should have defined sizes.
*   Recommended size: (Include size recommendation here)

## 13. Button Configuration
*   **Configurable button styles in `config/markdown-emails.php`.**
*   **Customizable properties: background_color, text_color, padding, border_radius, font_weight, margin.**
*   **Support for custom button types beyond the default set.**