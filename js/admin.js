jQuery(document).ready(function($) {
    var customFields = [];
    try {
        customFields = JSON.parse($('#clrf-custom-fields').val());
        if (!Array.isArray(customFields)) {
            customFields = [];
        }
    } catch (e) {
        customFields = [];
    }

    function renderFields() {
        $('#clrf-fields-list').empty();
        customFields.forEach(function(field, index) {
            $('#clrf-fields-list').append(
                '<li class="clrf-field" data-index="' + index + '" data-validation="' + field.validation + '">' +
                '<span>' + field.label + ' (' + field.type + ')</span> ' +
                '<a href="#" class="clrf-delete-field" data-index="' + index + '">Delete</a></li>'
            );
        });
    }
    renderFields();

    $('#clrf-add-field').click(function() {
        var fieldLabel = $('#clrf-field-label').val();
        var fieldType = $('#clrf-field-type').val();
        var fieldValidation = $('#clrf-field-validation').val();
        if (fieldLabel !== '') {
            var newField = { label: fieldLabel, type: fieldType, validation: fieldValidation };
            customFields.push(newField);
            $('#clrf-custom-fields').val(JSON.stringify(customFields));
            renderFields();
            $('#clrf-field-label').val('');
            $('#clrf-field-type').val('text');
            $('#clrf-field-validation').val('');
        }
    });

    $(document).on('click', '.clrf-delete-field', function(e) {
        e.preventDefault();
        var index = $(this).data('index');
        customFields.splice(index, 1);
        $('#clrf-custom-fields').val(JSON.stringify(customFields));
        renderFields();
    });

    $('#clrf-fields-list').sortable({
        update: function(event, ui) {
            var sortedFields = [];
            $('#clrf-fields-list li').each(function() {
                var index = $(this).data('index');
                sortedFields.push(customFields[index]);
            });
            customFields = sortedFields;
            $('#clrf-custom-fields').val(JSON.stringify(customFields));
            renderFields();
        }
    });
});
