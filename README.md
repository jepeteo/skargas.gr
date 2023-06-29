# Skargas.gr CRM

## login related functions

- **v_getUrl()** - get the current URL of a page
- **v_forcelogin()** - checks if a user is logged in and redirects them to the login page if they are not
- **redirect_admin()** - redirects the user to the homepage after login, specifically for administrators

## Shortcodes

### Randevou CPT
**[Show-Rantevou]** - Add the shortcode to a page to show the Rantevou CPT information. 
This includes, the form where you can filter the dates you want to search.
Also include basic info of all the scheduled "Rantevou", with pagination.

**[EditRandevou]** - provides a link to the "Rantevou" edit link (backend).

### Client CPT
**[ShowRandevousClientInformation]** - provides a link to the Client (CPT) frontend, attached to a Randevou (CPT)

**[ShowRandevousClient]** - Show the client information

**[showclients]** - Show all clients

**[EditClient]** - provides a link to the "Client" edit link (backend).

**[allfiles]** - show files attached to a client

**[maintenancehistory]** - show client's maintenance history.

**[image-gallery]** - show client's image gallery

**[phone_home]** - display client's home phone

**[phone_work]** - display client's work phone

**[phone_other]** - display client's other phone

**[mobile_personal]** - display client's personal mobile

**[mobile_work]** - display client's work mobile

**[mobile_other]** - display client's other mobile

**[client-info]** - show a list of all clients, with pagination, ready to print.

## Functions
**teo_filter_title()** - modifies the title of posts with the '**clients**' post type by appending additional client information retrieved from custom fields.

Using the following functions, we remove comment support from our wordpress.

- **teo_remove_admin_menus()**
- **teo_remove_comment_support()**
- **teo_remove_comments_admin_bar()**

**teo_remove_divi_project_post_type()** - remove 'project' CPT installed with theme Divi 
