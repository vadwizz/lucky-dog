DRUPAL 9
--------

user: afagioli, 21mar22

This is the Drupal 9 upgrade for  mmenu Drupal  module. 




Install:
  * module in the usual directory
  * download from [https://mmenujs.com/] and copy mmenu  library  inside /web/libraries. This module requires "/libraries/mmenu/dist/mmenu.css" and "/libraries/mmenu/dist/mmenu.js"
  * create a menu  called "mmenu".  you'll manage this menu at "/admin/structure/menu/manage/mmenu"  
  * customise "js/mmenu_fire.js" at will
  * customise "css/mmenu.css" at will
  * add a link inside your page.html.twig. IE "<a class="open-mmenu" href="#mmenu"><i class="fa fas fa-bars" aria-hidden="true"></i></a>". This link will show your mmenu
  * drush en mmenu
  
At this point you should have mmenu running
  




***************************************************************


From 2016, https://www.drupal.org/project/mmenu/releases/8.x-1.x-dev



ABOUT
--------------------------------------------------------------------------------
Mmenu is a mobile sliding menu module integrates the mmenu jQuery plugin [ https://mmenujs.com/ ]
for creating slick, app look-alike sliding menus for you mobile website.

REQUIREMENTS
--------------------------------------------------------------------------------
- Libraries module.

FEATURES
--------------------------------------------------------------------------------
The mmenu jQuery plugin has following features:
- Fully responsive CSS framework generated with SCSS.
- Creates sliding panels as easy as menus.
- Menu can be positioned at the top, right, bottom or left, at the back,
  front or next to the page.
- Use sliding horizontal or expanding vertical submenus.
- Optionally open the menu by dragging the page out of the viewport.
- Plays nicely with jQuery Mobile.
- Add headers, labels, counters and even a search field.
- Completely themable by changing the background-color.
- Works well on all major browsers.
- Filled with options for customizing the menu.
- Uses SCSS to easily create customized menus.

INSTALLATION
--------------------------------------------------------------------------------

 * install the  mmenu library available at https://mmenujs.com/download.html [free or buy]
 * create a menu named  'mmenu'  [ /admin/structure/menu/manage/mmenu ]
 * add a region named "mmenu" inside your THEME.info.yml
 * create  menu--mmenu.html.twig inside THEME/templates with "<nav id='mmenu'> ..... </nav>"
 * implement steps above via code :)
 
 <<<
