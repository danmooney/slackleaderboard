$('.tooltip-permalink').qtip({
    position: {
        my: 'bottom center',
        at: 'top center',
        viewport: $(window),
        adjust: {
            resize: true,
            y: -5
        }
    },
    style: {
        tip: {
            corner: true
        },
        classes: 'qtip-dark qtip-rounded qtip-shadow'
    },
    show: {
        delay: 200
    },
    hide: {
        fixed: true,
        delay: 50
    },
    content: {
        text: function (e, api) {
            var $el = $(this),
                teamDomain = window.location.pathname.split('/')[1] || SL_OPTIONS.demoTeamDomainFacade,
                url = '/' + teamDomain + '/permalink',
                data = {},
                oldTitle = $el.attr('oldtitle') || '',
                oldTitleArr,
                oldTitleHasReactionNameAndPercentageInBeginningOfStr,
                reactionNameAndPercentageInBeginningRegex = /(\s*[a-z_0-9:\+\-]+)(\s+)(\d+\.?\d*%)/,
                originalTooltipAnimation = api.options.position.effect,
                loadingStr = '<em>Loading...</em>'
            ;

            oldTitleHasReactionNameAndPercentageInBeginningOfStr = oldTitle.match(reactionNameAndPercentageInBeginningRegex);

            // format if old title has reaction name and percentage in beginning of oldTitle
            if (oldTitleHasReactionNameAndPercentageInBeginningOfStr) {
                oldTitleArr = oldTitle.replace(reactionNameAndPercentageInBeginningRegex, '$1!$3').split('!');
                // TODO - linkify next line?
                // oldTitleArr[0] = '<a href="' + '/' + teamDomain + '/r/' + old  '" class="tooltip-reaction-name">' + oldTitleArr[0] + '</a>'; // reaction emoji name
                oldTitleArr[0] = '<span class="tooltip-reaction-name">' + oldTitleArr[0] + '</span>'; // reaction emoji name

                oldTitleArr[1] = '<span class="tooltip-reaction-percentage">' + oldTitleArr[1] + '</span>'; // percentage
                oldTitle = oldTitleArr.join('');
            }

            if ($el.data('fetched-permalinks')) {
                return;
            }

            $el.data('fetched-permalinks', true);

            if ($el.data('reaction-id')) {
                data.reaction_id = $el.data('reaction-id');
            }

            if ($el.data('giver-user-id')) {
                data.giver_user_id = $el.data('giver-user-id');
            }

            if ($el.data('receiver-user-id')) {
                data.receiver_user_id = $el.data('receiver-user-id');
            }

            $.ajax({
                url: url,
                data: data,
                dataType: 'json'
            })
            .then(function (responseObj) {
                api.tooltip.css('visibility', 'hidden');

                // Set the tooltip content upon successful retrieval
                var htmlArr = [],
                    linkArr = responseObj.data
                ;

                if (oldTitle) {
                    htmlArr.push(oldTitle + '<br>');
                }

                htmlArr.push('<ul>');

                linkArr.forEach(function (link) {
                    var html,
                        anchorText,
                        linkMatch,
                        channelName,
                        fileUploader
                    ;

                    linkMatch = link.match(/slack\.com\/archives\/([^\/]+)/) || [];

                    if (channelName = linkMatch[1]) {
                        anchorText = '#' + channelName;
                    } else {
                        linkMatch = link.match(/slack\.com\/files\/([^\/]+)/) || [];

                        if (fileUploader = linkMatch[1]) {
                            anchorText = '@' + fileUploader;
                        }
                    }

                    if (!anchorText) {
                        return;
                    }

                    html = '<li><a target="_blank" href="' + link + '">' + anchorText + '</a></li>';
                    htmlArr.push(html);
                });

                // TODO
                // htmlArr.push('<a target="_blank" href="#">View more...</a>');
                htmlArr.push('</ul>');



                api.options.position.effect = false;
                api.set('content.text', htmlArr.join(''));

                api.reposition(null, false);
                api.tooltip.css('visibility', 'visible');
                api.options.position.effect = originalTooltipAnimation;
            }, function (xhr, status, error) {
                // Upon failure... set the tooltip content to the status and error value
                api.set('content.text', 'Oops!  Something went wrong :(');
            });

            if (oldTitle) {
                loadingStr = oldTitle + '<br>' + loadingStr;
            }

            return loadingStr; // Set some initial text
        }
    }
});