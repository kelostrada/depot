$(document).on('click', '.quantity-sub', (e) => {
    changeQuantity(e.target, -1);
});

$(document).on('click', '.quantity-add', (e) => {
    changeQuantity(e.target, 1);
});

function changeQuantity(target, value) {
    const id = $(target).data('id');
    $.post(`/admin/product/${id}/quantity`, {value: value}, (data) => {
        $(target).siblings('.quantity').html(data.quantity);
    });
}
