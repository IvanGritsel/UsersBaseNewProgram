import 'bootstrap/dist/css/bootstrap.min.css';
import 'jquery';

$(document).ready(function () {
    $('.checkbox').on('change', function () {
        if ($('.checkbox:checked').length !== 0) {
            $('#delete_selected_form').show();
        } else {
            $('#delete_selected_form').hide();
        }
    });

    $('.edit-button').on('click', function(event) {
        fillForEdit(event.target);
    });

    $('#alt_data_source_switch').on('click', function () {
        let sourceButton = $('#change_source_button');
        if (sourceButton.is(':visible')) {
            sourceButton.hide();
        } else {
            sourceButton.show();
        }
        let dbLabel = $('#db_option_label');
        let apiLabel = $('#api_option_label');

        let col = dbLabel.css('color');

        dbLabel.css('color', apiLabel.css('color'));
        apiLabel.css('color', col);
    });

    $('#check_uncheck').on('click', function () {
        checkUncheck();
    });

    $('#reset_button').on('click', function() {
        resetForm();
    });

    $('#submit').on('click', function() {
        return checkInput();
    });

    $('#delete_selected').on('click', function () {
        return deleteSelected();
    })
});

function fillForEdit(editButton) {
    $('#method').val('PUT');
    $('#edit_warning').show();
    $('#form').attr('action', ($('#alt_data_source').val() == 1 ? '/gorest' : '') + '/users/update');
    let idToEdit = editButton.value;
    $('#id').val(idToEdit);
    let row = document.getElementById(idToEdit).children;
    for (const cell of row) {
        if (cell.id === 'email') {
            $('#email_field').val(cell.innerHTML);
        } else if (cell.id === 'name') {
            $('#name_field').val(cell.innerHTML);
        } else if (cell.id === 'gender') {
            if (cell.innerHTML === 'Male') {
                $('#gender_field').val(1);
            } else {
                $('#gender_field').val(2);
            }
        } else if (cell.id === 'status') {
            if (cell.innerHTML === 'Active') {
                $('#status_field').val(1);
            } else {
                $('#status_field').val(2);
            }
        }
    }
}

function checkUncheck() {
    if ($('#check_uncheck').is(':checked')) {
        checkAll();
    } else {
        uncheckAll();
    }
    $('.checkbox').trigger('change');
}

function checkAll() {
    $('input:checkbox:not(:checked)').each(function () {
        if ($(this).attr('id') !== 'alt_data_source_switch') {
            $(this).prop('checked', true);
        }
    });
}

function uncheckAll() {
    $('input:checkbox:checked').each(function () {
        if ($(this).attr('id') !== 'alt_data_source_switch') {
            $(this).prop('checked', false);
        }
    });
}

function deleteSelected() {
    if (confirm('This action can not be undone. Proceed?')) {
        let hiddenFields = '';
        $('input:checkbox:checked').each(function () {
            if ($(this).val() !== 'on') {
                hiddenFields += '<input type="hidden" name="ids[]" value="' + $(this).val() + '">';
            }
        });
        $('#ids_to_delete').html(hiddenFields);
        return true;
    } else {
        return false;
    }
}

function showWarningMessage(message) {
    let warning = $('#input_warning');
    warning.html(message);
    if (!warning.is(':visible')) {
        warning.show();
    }
}

function checkInput() {
    let email = $('#email_field').val();
    let name = $('#name_field').val();
    let warningText = '';

    if (email == null || email === '' || name == null || name === '') {
        warningText += 'All fields must be filled';
        showWarningMessage(warningText);
        return false;
    } else if (!email.match(
        /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    )) {
        warningText += 'Invalid email';
        showWarningMessage(warningText);
        return false;
    }
    return true;
}

function resetForm() {
    $('#method').val('POST');
    $('#edit_warning').hide();
    $('#form').attr('action', ($('#alt_data_source').val() == 1 ? '/gorest' : '') + '/users/new');
    $('#id').val('');
}