# Contao Menu Bundle

This bundle adds a multi-level menu module for the Contao CMS.

## Features

- submenus are opened by hovering (or by tapping on touch devices)
- works both for mouse and for touch-based devices
- includes a preset template for bootstrap 4 (`nav_huh_menu_bs4`) and 5 (`nav_huh_menu_bs5`)
- define how many levels are collapsed
- define delays for opening and closing submenus

## Behavior

The menu is based on the `mouseover` event ("hover"). This way it works for both mouse *and* touch-based devices since `mouseover` events are triggered by tap events
on most touch devices.

On devices with `window.navigator.maxTouchPoints` being 0, you can also click the submenu headers because submenus are opened by hovering.

## Usage

1. Install the bundle via composer: `composer require heimrichhannot/contao-menu-bundle`
2. Update the database
3. Create a new module of type Menu (H&H)
4. Set the template nav_huh_menu_default to the module
5. Assign it to your layout (preferably in the header)

## Technical notes

### Events

Event             | Description
------------------|------------
`huhMenu:opening` | Thrown after the menu started opening
`huhMenu:opened`  | Thrown after the menu has been opened
`huhMenu:closing` | Thrown after the menu started closing
`huhMenu:closed`  | Thrown after the menu has been closed

### Why can't submenu headers be clicked on multi-touch devices?

Because then submenu entries could never be reached.

### On Microsoft Surface

Even in tablet mode the device doesn't throw `touch` but `mouse` events. Also it masquerades as a desktop computer with mouse. Hence it tells the browser
that it supports `hovering` (`Modernizr.hovermq` is `true`). So if we'd allow that submenu headers can be tapped and directly opened, this would not only happen
if a mouse is attached but also in tablet mode while the user uses his finger. In this case submenu entries could never be reached.

### The menu closes when I hover above a submenu

HTML document have to be in `standards` mode. Make sure <!DOCTYPE html> is set!

This is in most cases caused by CSS margins. Please only use paddings to adjust dimensions of the menus items. Please make sure that submenus (ul and li) take the space
where the cursor can be over.

## Known issues

### What about smaller screen dimensions?

Currently this menu is only for dimensions like tablet landscape and larger. So please use another module for this case. Nevertheless it's planned for this bundle
to support bootstrap's mobile menu for smaller screen dimensions out of the box.
