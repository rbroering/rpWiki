/**
 * 
 */
function getGroups() {
    var groups  = [];

    $('#table_groups .checkbox input.check-hidden').each(function() {
        if ($(this).is(':checked')) {
            groups.push(($(this).attr('id')));
        }
    });

    return groups;
}

/**
 * 
 */
function getTypes() {
    var types  = [];

    $('#table_types .checkbox input.check-hidden').each(function() {
        if ($(this).is(':checked')) {
            types.push(($(this).attr('id')));
        }
    });

    return types;
}

$(document).ready(function() {
    var groups_pre  = getGroups();
    var types_pre   = getTypes();

    var groups_post = [];
    var types_post  = [];

    $('.checkbox input.check-hidden').on('change', function() {
        groups_post = getGroups();
        types_post  = getTypes();

        groups_added    = groups_post.filter(group => !groups_pre.includes(group));
        groups_removed  = groups_pre.filter(group => !groups_post.includes(group));

        types_added     = types_post.filter(type => !types_pre.includes(type));
        types_removed   = types_pre.filter(type => !types_post.includes(type));

        console.log(groups_added);
        console.log(groups_removed);

        groups_str = "Changes: ";

        if (groups_added.length === 0 && groups_removed.length === 0) {
            groups_str += "(None)";
        } else {
            groups_added.forEach(group => {
                groups_str += "+" + group + ", ";
            });
            groups_removed.forEach(group => {
                groups_str += "-" + group + ", ";
            });
            groups_str = groups_str.substr(0, groups_str.length - 2);
        }

        console.log(groups_str);
    });
});