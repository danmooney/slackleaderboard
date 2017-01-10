var emojis = {};
document.querySelectorAll('[data-name]').forEach(function (emoji) {
    var headline = $(emoji).closest('.emoji_section_div').children('h3');
    // emoji_h3_mine is 'frequently used', emoji_h3_slack is 'custom'
    if (headline.attr('id') === 'emoji_h3_mine' || headline.attr('id') === 'emoji_h3_slack') {
        return;
    }

    // if you want aliases too
    emoji.getAttribute('data-names').split(' ').forEach(function (name) {
        name = name.trim();
        emojis[name] = emoji.querySelector('.emoji').style.backgroundImage.replace('url("', '').replace('")', '')
    });

    // default only
    // emojis[emoji.getAttribute('data-name')] = emoji.querySelector('.emoji').style.backgroundImage.replace('url("', '').replace('")', '')
});