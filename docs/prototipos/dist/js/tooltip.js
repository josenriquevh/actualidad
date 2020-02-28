$("[data-toggle=popover]").popover({
    html : true,
    trigger: 'focus',
    content: function() {
        var content = $(this).attr("data-popover-content");
        return $(content).children(".popover-body").html();
    }
});