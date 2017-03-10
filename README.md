# Trello
Trello integration for Drupal

This module integrates with the Trello API and provides a report from the Trello cards in any Trello board list. For instance, if you have a board that has a 'Doing' and 'Done' list, you could export the cards in the Done list into a report before you archive them at the end of a week or a sprint. You could then send this report to people who don't have access to Trello, paste it into a journal, or otherwise keep track of what got done.

You can retrieve any list on any board you have access to. If the data you want to export is not in a single list but scattered around, you could create a single temporary list for anything you want to export, move cards into it, and export from that list.

Each user has their own key and token that allows them to retrieve Trello data that they have access to. To get the key and token, log into Trello and go to [https://trello.com/app-key](https://trello.com/app-key). There you will see your key, and a button to generate a token.

This module provides two fields for the user, `field_trello_key` and `field_trello_token`. Although the fields are provided, they will be hidden until you reveal them. Go to `/admin/config/people/accounts/form-display` and make sure the fields are displayed on the edit form, and to `/admin/config/people/accounts/` to display them on the user profile. Go to your profile page and enter the key and token you got from Trello and save them.

Finally, go to the `Trello` tab on the user profile to retrieve your Trello data. 

1. You will see a list of Trello boards you have access to. Select the board you want to pull data from. 
2. You will then see the lists on the selected board. Select a list.
3. Click on the submit button to retreive the card data from the selected list in a nice report.

If you want to futher tweak the report, you can copy the template, `trello-export.html.twig`, into your theme and make changes to it.