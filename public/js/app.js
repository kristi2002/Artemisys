$(document).ready(function () {

    // ===== SIDEBAR TOGGLE (mobile) =====
    $('#sidebarToggle').on('click', function () {
        $('#sidebar').toggleClass('show');
        if ($('#sidebar').hasClass('show')) {
            $('<div class="sidebar-overlay show"></div>').appendTo('body').on('click', function () {
                $('#sidebar').removeClass('show');
                $(this).remove();
            });
        } else {
            $('.sidebar-overlay').remove();
        }
    });

    // ===== SIDEBAR SUBMENU TOGGLE =====
    $('.submenu-toggle').on('click', function (e) {
        e.preventDefault();
        $(this).closest('.has-submenu').toggleClass('open');
    });

});
