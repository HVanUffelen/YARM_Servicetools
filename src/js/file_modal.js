;(function ($) {
    var onready = function () {
        var local_name;

        $(document).on('click', '.editFile', function (e) {
            e.preventDefault();
            var name = $(this).attr('data-name');
            local_name = $(this).attr('data-local_name');
            var id = $(this).attr('data-id');
            var form = $(document).find('#editFileForm');
            form.find('.local_name').val(local_name);
            form.find('.name').val(name);
            form.find('.id').val(id);
            $('#modal').modal('show');
        });

        $(document).on('click', '#btn-file-update', function (e) {
            e.preventDefault();
            var form = $('#editFileForm');
            form.validate();
            if (form.valid()) {
                var formData = form.serializeArray();
                var id = formData.find(fd => fd.name == 'id').value.trim();
                var name = formData.find(fd => fd.name == 'name').value.trim();
                var new_local_name = formData.find(fd => fd.name == 'local_name').value.trim();

                $.ajax({
                    type: 'POST',
                    url: '/' + YARM.sys_name + '/change_file',
                    data: { id, name, local_name, new_local_name },
                    success: function (data) {
                        var file = data[0];
                        var localFileName = data[1];
                        var fileExists = data[2];
                        var row = $(document).find('#tblData-id_' + file.id);
                        if (file.name == localFileName && fileExists) {
                            row.remove();
                        } else {
                            row.find('.C-name').html(file.name);
                            row.find('.C-local_name').html(localFileName);
                        }
                    },
                    error: function (error) {
                        console.error(error);
                    }
                });

                $('#modal').modal('hide');
            }
        });
     };
     $(document).ready(onready);
}(jQuery));
