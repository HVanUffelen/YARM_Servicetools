;(function ($) {
    var onready = function () {
        $(document).on('click', '.edit-COP', function (e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            $.ajax({
                type: 'GET',
                data: {id},
                url: '/dlbt/getCOP',
                success: function (data) {
                    var form = $('#modal').parents('form');

                    for (var attr in data) {
                        form.find('#' + attr).val(data[attr]);
                    }

                    $('#modal').modal('show');
                }
            });
        });

        $(document).on('click', '#btn-coPublication-save', function (e) {
            e.preventDefault();
            var form = $('#commentsOnPublicationForm');
            form.validate();
            if (form.valid()) {
                $.ajax({
                    type: 'POST',
                    data: form.serialize(),
                    url: '/dlbt/editCOP',
                    success: function (data) {
                        var row = $(document).find('#tblData-id_' + data.ref_id);
                        for (var attr in data) {
                            row.find('.C-' + attr).html(data[attr]);
                        }
                    }
                });
                $('#modal').modal('hide');
            }
        });
     };
     $(document).ready(onready);
}(jQuery));
