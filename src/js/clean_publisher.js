;(function ($) {
    var onready = function () {
        $(document).on('change', '.publisherCheckbox', function (e) {
            e.preventDefault();
            var checkboxes = $(document).find('.publisherCheckbox:checked');
            if (checkboxes.length > 0) {
                $('#editPublisher').attr('disabled', false);
                if ($('#editPublisher').val()) {
                    $('#confirmPublisher').attr('disabled', false);
                }
            } else {
                $('#editPublisher').attr('disabled', true);
                $('#confirmPublisher').attr('disabled', true);
            }
        });

        $(document).on('keyup', '#editPublisher', function (e) {
            e.preventDefault();
            if ($('#editPublisher').val()) {
                $('#confirmPublisher').attr('disabled', false);
            } else {
                $('#confirmPublisher').attr('disabled', true);
            }
        });
     };
     $(document).ready(onready);
}(jQuery));
