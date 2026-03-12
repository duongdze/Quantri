document.addEventListener('DOMContentLoaded', function () {
    const currentUrl = window.location.href;
    const urlParams = new URLSearchParams(window.location.search);
    const currentAction = urlParams.get('action') || '';

    console.log('=== SIDEBAR HIGHLIGHT ===');
    console.log('Current URL:', currentUrl);
    console.log('Current Action:', currentAction || '(none - dashboard)');

    // function highlightActiveMenus() {
    //     // Remove active class from ALL nav links and dropdown toggles
    //     document.querySelectorAll('.nav-link').forEach(link => {
    //         link.classList.remove('active');
    //     });
    //     document.querySelectorAll('.dropdown-toggles').forEach(toggle => {
    //         toggle.classList.remove('active');
    //     });

    //     // Get all nav links that have href attributes
    //     const navLinks = Array.from(document.querySelectorAll('a.nav-link[href]'));
    //     let matchFound = false;

    //     navLinks.forEach(link => {
    //         const href = link.getAttribute('href');
    //         if (!href) return;

    //         // Extract the action from the link's href
    //         try {
    //             const linkUrl = new URL(href, window.location.origin);
    //             const linkAction = linkUrl.searchParams.get('action') || '';

    //             console.log('Checking link:', {href: href, linkAction: linkAction});

    //             // Match logic:
    //             // 1. If current has no action (dashboard) -> match links with no action
    //             // 2. If current has action -> match links with same base action
                
    //             if (!currentAction && !linkAction) {
    //                 // Both are dashboard
    //                 console.log('✓ Dashboard match');
    //                 link.classList.add('active');
    //                 matchFound = true;
    //             } else if (currentAction && linkAction) {
    //                 // Both have actions - compare base actions
    //                 const currentBase = currentAction.split('/')[0];
    //                 const linkBase = linkAction.split('/')[0];
                    
    //                 if (currentBase === linkBase) {
    //                     console.log('✓ Action match:', currentBase);
    //                     link.classList.add('active');
    //                     matchFound = true;
    //                 }
    //             }
    //         } catch (e) {
    //             console.log('Error parsing URL:', href, e);
    //         }
    //     });

    //     if (!matchFound && !currentAction) {
    //         console.log('No match found, defaulting to dashboard');
    //         // If no match and we're on dashboard, find and highlight dashboard link
    //         const dashboardLink = navLinks.find(link => {
    //             try {
    //                 const linkUrl = new URL(link.getAttribute('href'), window.location.origin);
    //                 return !linkUrl.searchParams.get('action');
    //             } catch (e) {
    //                 return false;
    //             }
    //         });
            
    //         if (dashboardLink) {
    //             console.log('✓ Found dashboard link to highlight');
    //             dashboardLink.classList.add('active');
    //         }
    //     }

    //     // Now highlight dropdown toggles if they contain active items
    //     document.querySelectorAll('.dropdown-toggles').forEach(toggle => {
    //         const collapseId = toggle.getAttribute('data-collapse-id');
    //         const collapseEl = document.getElementById(collapseId);
            
    //         if (collapseEl && collapseEl.querySelector('.nav-link.active')) {
    //             console.log('✓ Highlighting dropdown toggle:', toggle.getAttribute('data-menu-key'));
    //             toggle.classList.add('active');
    //         }
    //     });
    // }

    // Handle dropdown toggles - auto-expand relevant dropdowns
    // Lấy tất cả các link trong menu
    // const navLinks = document.querySelectorAll('.nav-link');
    
    // navLinks.forEach(link => {
    //     // Kiểm tra nếu href của link khớp với URL hiện tại
    //     if (link.href === window.location.href) {
    //         link.classList.add('active');
    //     } else {
    //         link.classList.remove('active');
    //     }
    // });
    document.querySelectorAll('.dropdown-toggles').forEach(toggle => {
        const collapseId = toggle.getAttribute('data-collapse-id');
        const collapseEl = document.getElementById(collapseId);
        const menuKey = toggle.getAttribute('data-menu-key');

        if (!collapseEl) return;

        // Auto-open if current action matches this menu
        // Check exact match OR starts with menuKey followed by slash (e.g., "tours/edit" matches "tours" but "tours_logs" does not)
        const isExactMatch = currentAction === menuKey;
        const isSubAction = currentAction.startsWith(menuKey + '/');
        
        if (currentAction && (isExactMatch || isSubAction)) {
            console.log('Auto-opening dropdown:', menuKey);
            new bootstrap.Collapse(collapseEl, {toggle: false}).show();
            toggle.setAttribute('aria-expanded', 'true');
        }

        // Handle toggle clicks
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            const bsCollapse = new bootstrap.Collapse(collapseEl, {toggle: true});
            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', !isExpanded);
        });
    });

    // Run highlighting on page load
    highlightActiveMenus();

    // Update on back/forward buttons
    window.addEventListener('popstate', highlightActiveMenus);
});
