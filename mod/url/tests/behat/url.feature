@mod @mod_url
Feature: An admin can enable repository and add a google doc url via the google docs API

  Background:
    Given the following "courses" exist:
      | fullname  | shortname |
      | Course1   | c1        |
    When I log in as "admin"
    And I navigate to "Manage repositories" node in "Site administration > Plugins > Repositories"
    And I click on "#applytogoogledocs select" "css_element"
    And I click on "#applytogoogledocs select option[value='newon']" "css_element"
    And I set the following fields to these values:
      | pluginname | GoogleDocs |
      | clientid |  485434265381-sumt71vc6d1ecajs243me5hk009vmgs0.apps.googleusercontent.com |
      | secret |  RFrXDQE_3swodR31nC8iMEHP |
    And I press "Save"

  @javascript
  Scenario: Jvascript enabled
    When I am on site homepage
    And I follow "Course1"
    And I turn editing mode on
    When I add a "URL" to section "1"
    And I set the following fields to these values:
      | Name | Google Doc API url|
      | Description | Build a url using google doc |
    And I press "Choose a link..."
    And I click on ".fp-repo-area li a img[src*='/repository_googledocs/']" "css_element"
    And I press "Login to your account"
    And I switch to "repo_auth" window
    And I set the following fields to these values:
      | email | behatTest2@gmail.com |
    And I press "next"
    And I set the following fields to these values:
      | Passwd | behatTest |
    And I press "Sign in"
    And I wait "2" seconds
    And I press "Allow"
    And I switch to the main window
    Then I click on ".fp-content img[title='Test Doc.rtf']" "css_element"
    And I press "Select this file"
    And I press "Save and return to course"
    And I click on ".section .url .instancename" "css_element"
    Then "#docs-drive-logo" "css_element" should exist


