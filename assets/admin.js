jQuery(document).ready(function($) {
    let ruleIndex = $('#rules-container .rule-row').length;
    
    $('#add-rule').on('click', function() {
        const newRow = `
            <tr class="rule-row">
                <td>
                    <input type="text" 
                           name="przedawniacz_settings[${ruleIndex}][post_type]" 
                           value="" required>
                </td>
                <td>
                    <input type="number" 
                           name="przedawniacz_settings[${ruleIndex}][days]" 
                           value="30" min="1" required>
                </td>
                <td>
                    <select name="przedawniacz_settings[${ruleIndex}][action]">
                        <option value="delete">Usuń</option>
                        <option value="quarantine">Kwarantanna</option>
                    </select>
                </td>
                <td>
                    <input type="number" 
                           name="przedawniacz_settings[${ruleIndex}][quarantine_days]" 
                           value="30" min="1">
                </td>
                <td>
                    <button type="button" class="button remove-rule">Usuń</button>
                </td>
            </tr>
        `;
        
        $('#rules-container').append(newRow);
        ruleIndex++;
    });
    
    $(document).on('click', '.remove-rule', function() {
        $(this).closest('.rule-row').remove();
    });
    
    $(document).on('change', 'select[name$="[action]"]', function() {
        const quarantineField = $(this).closest('tr').find('input[name$="[quarantine_days]"]');
        if ($(this).val() === 'quarantine') {
            quarantineField.prop('required', true);
        } else {
            quarantineField.prop('required', false);
        }
    });
});