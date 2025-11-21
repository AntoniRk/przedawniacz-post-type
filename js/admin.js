jQuery(document).ready(function ($) {
    let ruleIndex = $('#rules-list .rule-row').length;

    // Dodawanie nowej reguły
    $('#add-rule').on('click', function () {
        const template = $('#rule-template').html();
        const newRule = template.replace(/{{INDEX}}/g, ruleIndex);

        $('#rules-list').append(newRule);

        // Włącz przyciski usuwania dla wszystkich reguł oprócz pierwszej
        updateRemoveButtons();

        ruleIndex++;
    });

    // Usuwanie reguły
    $(document).on('click', '.remove-rule', function () {
        if ($('.rule-row').length > 1) {
            $(this).closest('.rule-row').fadeOut(300, function () {
                $(this).remove();
                updateRemoveButtons();
                reindexRules();
            });
        }
    });

    // Aktualizuj stan przycisków usuwania
    function updateRemoveButtons() {
        const ruleCount = $('.rule-row').length;

        if (ruleCount === 1) {
            $('.remove-rule').prop('disabled', true);
        } else {
            $('.remove-rule').prop('disabled', false);
        }
    }

    // Przeindeksuj reguły po usunięciu
    function reindexRules() {
        $('.rule-row').each(function (index) {
            $(this).attr('data-index', index);

            // Aktualizuj nazwy pól
            $(this).find('select').attr('name', 'rules[' + index + '][post_type]');
            $(this).find('input[type="number"]').attr('name', 'rules[' + index + '][days]');
        });

        ruleIndex = $('.rule-row').length;
    }

    // Walidacja przed zapisem
    $('form').on('submit', function (e) {
        let czyPoprawne = true;
        const uzywanePostTypes = [];

        $('.rule-row').each(function () {
            const postType = $(this).find('select').val();
            const days = $(this).find('input[type="number"]').val();

            // Sprawdź czy wszystkie pola są wypełnione
            if (!postType || !days || days < 1) {
                czyPoprawne = false;
                $(this).css('border-color', '#dc3232');
                return;
            } else {
                $(this).css('border-color', '#ddd');
            }

            // Sprawdź duplikaty
            if (uzywanePostTypes.includes(postType)) {
                alert('Nie możesz dodać dwóch reguł dla tego samego typu wpisu: ' + postType);
                czyPoprawne = false;
                return false;
            }

            uzywanePostTypes.push(postType);
        });

        if (!czyPoprawne) {
            e.preventDefault();
            alert('Uzupełnij wszystkie wymagane pola i upewnij się, że nie ma duplikatów.');
        }
    });

    // Podświetlanie pustych pól
    $(document).on('change', '.rule-row select, .rule-row input', function () {
        const $row = $(this).closest('.rule-row');
        const postType = $row.find('select').val();
        const days = $row.find('input[type="number"]').val();

        if (postType && days && days > 0) {
            $row.css('border-color', '#46b450');
            setTimeout(function () {
                $row.css('border-color', '#ddd');
            }, 1000);
        }
    });

    // Inicjalizacja - sprawdź stan przycisków
    updateRemoveButtons();
});