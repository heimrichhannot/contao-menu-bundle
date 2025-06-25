class MenuBundle {

    static init() {
        MenuBundle.initMenu();
    }

    static initMenu() {
        document.querySelectorAll('.mod_huh_menu').forEach((menu) => {
            let maxTriggerLevel = menu.getAttribute('data-max-trigger-level'),
                closeDelay = menu.getAttribute('data-close-delay'),
                closingDuration = menu.getAttribute('data-closing-duration'),
                triggerClassesForChildfreeLevel1 = menu.getAttribute('data-trigger-classes-for-childfree-level1'),
                triggerActivatedLinkClasses = [];

            if (maxTriggerLevel == 0) {
                triggerActivatedLinkClasses.push('a.submenu');
            } else {
                for (let i = 1; i <= maxTriggerLevel; i++) {
                    triggerActivatedLinkClasses.push('.level_' + i + ' > .submenu > a.submenu');
                }
            }

            if (triggerClassesForChildfreeLevel1 === '1') {
                triggerActivatedLinkClasses.push('.level_1 > .nav-item > a.nav-link');
            }

            let links = menu.querySelectorAll(triggerActivatedLinkClasses),
                closeBlockingElements = [];

            links.forEach((link) => {
                closeBlockingElements.push(link);
                closeBlockingElements.push(link.nextElementSibling);
            });

            links.forEach((link) => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();

                    // if mouse cursor or the menu has no sub pages
                    if (window.navigator.maxTouchPoints == 0 || !link.classList.contains('submenu')) {
                        let url = link.getAttribute('href');

                        if (url !== null && url.length > 0) {
                            location.href = link.href;
                        }
                    }
                });

                link.addEventListener('mouseover', () => {
                    MenuBundle.openSubmenu(menu, link);
                });

                link.addEventListener('touchstart', () => {
                    MenuBundle.openSubmenu(menu, link);
                });

                link.addEventListener('mouseleave', (e) => {
                    if (MenuBundle.isElementCurrentlyHovered(link) || MenuBundle.isElementCurrentlyHovered(link.nextElementSibling)) {
                        return;
                    }

                    setTimeout(() => {
                        if (MenuBundle.isElementCurrentlyHovered(link) || MenuBundle.isElementCurrentlyHovered(link.nextElementSibling)) {
                            return;
                        }

                        MenuBundle.addClosingClassToElements([link, link.nextElementSibling]);
                        MenuBundle.addClosingClassToMenu(menu, closeBlockingElements);

                        setTimeout(() => {
                            MenuBundle.removeClassFromElements('closing', [link, link.nextElementSibling])

                            if (MenuBundle.isElementCurrentlyHovered(link) || MenuBundle.isElementCurrentlyHovered(link.nextElementSibling)) {
                                return;
                            }

                            MenuBundle.removeClassFromElements('open', [link, link.nextElementSibling]);
                            MenuBundle.removeMenuOpenState(menu, closeBlockingElements);
                        }, closingDuration);
                    }, closeDelay);
                });

                if (null !== link.nextElementSibling) {
                    link.nextElementSibling.addEventListener('mouseleave', (e) => {
                        if (MenuBundle.isElementCurrentlyHovered(link) || MenuBundle.isElementCurrentlyHovered(link.nextElementSibling)) {
                            return;
                        }

                        setTimeout(() => {
                            if (MenuBundle.isElementCurrentlyHovered(link) || MenuBundle.isElementCurrentlyHovered(link.nextElementSibling)) {
                                return;
                            }

                            MenuBundle.addClosingClassToElements([link, link.nextElementSibling]);
                            MenuBundle.addClosingClassToMenu(menu, closeBlockingElements);

                            setTimeout(() => {
                                MenuBundle.removeClassFromElements('closing', [link, link.nextElementSibling])

                                if (MenuBundle.isElementCurrentlyHovered(link) || MenuBundle.isElementCurrentlyHovered(link.nextElementSibling)) {
                                    return;
                                }

                                MenuBundle.removeClassFromElements('open', [e.target, link]);
                                MenuBundle.removeMenuOpenState(menu, closeBlockingElements);
                            }, closingDuration);
                        }, closeDelay);
                    });
                }
            });

            document.querySelector('body').addEventListener('touchstart', (e) => {
                if (e.target.closest('.mod_huh_menu') !== null) {
                    return;
                }

                setTimeout(() => {
                    if (MenuBundle.isElementCurrentlyHovered(menu)) {
                        return;
                    }

                    // remove all open classes
                    menu.querySelectorAll('.open').forEach((element) => {
                        element.classList.remove('open');
                    });

                    MenuBundle.removeMenuOpenState(menu, closeBlockingElements);
                }, closeDelay);
            });
        });
    }

    static getAllParentNodes(node) {
        var parents = [];

        while (node) {
            parents.unshift(node);
            node = node.parentNode;
        }

        for (var i = 0; i < parents.length; i++) {
            if (parents[i] === document) {
                parents.splice(i, 1);
            }
        }

        return parents;
    }

    static openSubmenu(menu, link) {
        let openDelay = menu.getAttribute('data-open-delay') ? menu.getAttribute('data-open-delay') : 0,
            openingDuration = menu.getAttribute('data-opening-duration') ? menu.getAttribute('data-opening-duration') : 0;

        if (link.classList.contains('open')) {
            return;
        }

        setTimeout(() => {
            if (!MenuBundle.isElementCurrentlyHovered(link)) {
                return;
            }

            MenuBundle.addOpeningClassToElements([menu, link, link.nextElementSibling]);

            setTimeout(() => {
                MenuBundle.removeClassFromElements('opening', [menu, link, link.nextElementSibling])

                if (!MenuBundle.isElementCurrentlyHovered(link)) {
                    return;
                }

                if (!menu.classList.contains('open')) {
                    menu.classList.add('open');

                    document.dispatchEvent(new CustomEvent('huhMenu:opened', {detail: menu, bubbles: true, cancelable: true}));
                }

                let openedParents = [];

                MenuBundle.getAllParentNodes(link).forEach((element) => {
                    if (element.classList.contains('open')) {
                        openedParents.push(element);
                    }
                });

                // remove all open classes except those which are parents
                menu.querySelectorAll('.open').forEach((element) => {
                    if (openedParents.indexOf(element) < 0) {
                        let remove = true;

                        Array.from(element.parentNode.children).forEach(child => {
                            if (openedParents.indexOf(child) < 0) {
                                remove = false;
                            }
                        });

                        if (remove) {
                            element.classList.remove('open');
                        }
                    }
                });

                if (!link.classList.contains('open')) {
                    link.classList.add('open');
                }

                if (link.nextElementSibling !== null && !link.nextElementSibling.classList.contains('open')) {
                    link.nextElementSibling.classList.add('open');
                }
            }, openingDuration);
        }, openDelay);
    }

    static isElementCurrentlyHovered(element) {
        let found = false;

        if (element === null) {
            return false;
        }

        document.querySelectorAll( ':hover' ).forEach((hoveredElement) => {
            if (hoveredElement === element) {
                found = true;
            }
        });

        return found;
    }

    static removeMenuOpenState(menu, closeBlockingElements) {
        let openedElements = menu.querySelectorAll('.open'),
            block = false;

        menu.classList.remove('closing');

        for (let i = 0; i < closeBlockingElements.length; i++) {
            if (MenuBundle.isElementCurrentlyHovered(closeBlockingElements[i])) {
                block = true;
                break;
            }
        }

        if (!block && openedElements.length < 1) {
            menu.classList.remove('open');

            document.dispatchEvent(new CustomEvent('huhMenu:closed', {detail: menu, bubbles: true, cancelable: true}));
        }
    }

    static addClosingClassToMenu(menu, closeBlockingElements) {
        let openedElements = menu.querySelectorAll('.open'),
            block = false;

        for (let i = 0; i < closeBlockingElements.length; i++) {
            if (MenuBundle.isElementCurrentlyHovered(closeBlockingElements[i])) {
                block = true;
                break;
            }
        }

        if (!block && openedElements.length > 0 && !menu.classList.contains('closing') && menu.classList.contains('open')) {
            menu.classList.add('closing');

            document.dispatchEvent(new CustomEvent('huhMenu:closing', {detail: menu, bubbles: true, cancelable: true}));
        }
    }

    static addOpeningClassToElements(elements) {
        elements.forEach((element) => {
            if (null !== element && !element.classList.contains('opening') && !element.classList.contains('open')) {
                if (element.classList.contains('mod_huh_menu')) {
                    document.dispatchEvent(new CustomEvent('huhMenu:opening', {detail: element, bubbles: true, cancelable: true}));
                }

                element.classList.add('opening');
            }
        });
    }

    static removeClassFromElements(className, elements) {
        elements.forEach((element) => {
            if (element !== null) {
                element.classList.remove(className);
            }
        });
    }

    static addClosingClassToElements(elements) {
        elements.forEach((element) => {
            if (null !== element && !element.classList.contains('closing') && element.classList.contains('open')) {
                element.classList.add('closing');
            }
        });
    }
}

export {MenuBundle};
