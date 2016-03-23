@repository @repository_googledocs @javascript
Feature: Adding Google Drive as a link or shortcut in File resource.

  Background:
    Given the following "courses" exist:
      | fullname  | shortname |
      | Course1   | c1        |
    And Google Drive repository is enabled
    And I log in as "admin"
    And I am on site homepage
    And I follow "Course1"
    And I turn editing mode on

  Scenario: Creating shortcut 
    When I add a "File" to section "0"
    And I set the following fields to these values:
      | Name        | Create an alias/shortcut to a gdoc file |
      | Description | Create an alias/shortcut to a gdoc file |
    And I press "Add..."
    And I click on ".fp-repo-area li a img[src*='/repository_googledocs/']" "css_element"
    And I login to Google Drive
    And I click on ".fp-content img[title='Test Doc.rtf']" "css_element"
    # Choosing "Create an alias/shortcut to the file"
    And I click on ".file-picker input[name='linktype'][value='4']" "css_element"
    And I press "Select this file"
    And I press "Save and return to course"
    And I follow "Create an alias/shortcut to a gdoc file"
    Then "#docs-drive-logo" "css_element" should exist

  Scenario: Choosing force download
    When I add a "File" to section "1"
    # Choosing "Force download"
    And I set the following fields to these values:
      | Name        | Make a copy of gdoc file |
      | Description | Copy a google doc        |
      | Display     | 4                        |
    And I press "Add..."
    And I click on ".fp-repo-area li a img[src*='/repository_googledocs/']" "css_element"
    And I login to Google Drive
    And I click on ".fp-content img[title='Test Doc.rtf']" "css_element"
    And I press "Select this file"
    And I press "Save and return to course"
    Then following "Make a copy of gdoc file" should download between "9000" and "10000" bytes
