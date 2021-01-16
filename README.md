# Elements:
 - Francisco Gonçalves (201704790) 
 - José Silva (201705591)
 - Victor Nunes (201907226)
 
# Credentials (username/password (role))
 - user1/password (client)
 - user2/password (client)
 - user3/password (client)
 - user4/password (client)

# Libraries:
- none

# Features:
 - Security
     - XSS: yes
     - CSRF: yes
     - SQL using prepare/execute: yes
     - Passwords: BCRYPT
     - Data Validation: regex / php / html / javascript / ajax
     - Other: Remember me Cookies automatically delete if the validator of a given selector is wrong
 - Technologies
     - Separated logic/database/presentation: yes
     - Semantic HTML tags: yes
     - Responsive CSS: yes
     - Javascript: yes
     - Ajax: yes
     - REST API: no
     - Other: Remember Me cookies (ellaborated in the additional features)
     
  Usability:
     - Error/success messages: yes
     - Forms don't lose data on error: yes

# Additional Features
- Remember Me
    - Based on paragonie's 2016 best recommendations: https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence#title.2
      * Section "Proactively Secure Long-Term User Authentication"
    - There's a `selector` that identifies the user and a `validator` that confirms the cookie is correct
      * Cookies last 1 month
      * If the validator for a given seelctor is invalid the database entry is deleted
- Form Creator
    - Easy way of generating forms with automatic CSRF included (tries to replicate what is seen in modern frameworks such as Laravel,Django,..)
    - Supports regular old forms and ajax too!
    - Can generate pop-up forms or inline!
    - The two main exported functions are `add_input` and `add_select`
      * `add_input` adds an arbitrary input field with all of the HTML attributes configurable by the user
      * `add_select` exact same thing but for dropdowns
- Pagination
    - Configurable class that allows the user to define the number of elements per page
    - Automatically appends to the query the `LIMIT` and `OFFSET`
    - Generates the appropriate JS/HTML for the `current_page`
- Socials
    - Draws the pet cards
    - Detects whether the user is logged-in to heart pictures
- Security
    - Exports two functions `test_csrf` and `guarantee_and_escape`
    - `test_csrf` compares the given CSRF token with the one from the current sesion
      * Automatically generates the appropriate error message
    - `guarantee_and_escape`, guarantees that the given fields exist and if they do they're escaped

# Notes about features

## Proposals

- Incoming
  * Current open incoming proposal
- Outgoing
  * Current open outgoing proposal
- Previous
  * Closed outgoing proposals

## Change credentials

Username and "Old Password" are the only required fields, "New Password" is only needed in case you want to change the password.
To change the username it's just needed to modify the field.
