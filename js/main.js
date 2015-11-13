$(document).ready(function() {
    $.material.init(); //Initialize Material Design JS
});

$('#logout').click(function() {
    var input = confirm("Are you sure you want to logout?");
    if (input == true) {
        window.location = 'logout.php';
    }
});

$('[id=add-btn]').click(function() {
    var id = $(this).val();
    $.post('add_item.php', {item: id})
        .done(function() {
            alert("Added to cart!");
        });;
});

$('[id=del-btn]').click(function() {
    var id = $(this).val();
    $.post('del_item.php', {item: id})
        .done(function() {
            window.location.reload();
        });
});

$('#checkout-btn').click(function() {
    $.post('checkout.php')
        .done(function() {
            window.location = 'history.php';
        });
});
