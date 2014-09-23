$(function() {
    $(".closedsection").hide();
});

function connectDialog(selector, url) {
    //.html('<iframe style="border: 0px; " src="' + url + '" width="100%" height="100%"></iframe>')
    var dialog = $('<div></div>')
            .load(url)
        
        .dialog({
            autoOpen: false,
            modal: true,
            height: 625,
            width: 500,
            title: "Some title",
            beforeClose: function() {
                location.reload();
            },
        });

    selector.click(function() {
        dialog.dialog('open');
        return false;
    });
}
