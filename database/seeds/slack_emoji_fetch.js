var emojis = {}; document.querySelectorAll('[data-name]').forEach(function (emoji) {var headline = $(emoji).closest('.emoji_section_div').children('h3'); if (headline.attr('id') === 'emoji_h3_mine' || headline.attr('id') === 'emoji_h3_slack') {return;} emojis[emoji.getAttribute('data-name')] = emoji.querySelector('.emoji').style.backgroundImage.replace('url("', '').replace('")', '')})