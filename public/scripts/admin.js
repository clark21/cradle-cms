jQuery(function($) {
    /**
     * General Search
     */
    (function() {
        /**
         * Search filter more less toggle
         */
        $(window).on('search-filter-toggle-click', function(e, target) {
            if($('i', target).hasClass('fa-caret-down')) {
                $('i', target)
                    .removeClass('fa-caret-down')
                    .addClass('fa-caret-up');
                $('span', target).html('less');
            } else {
                $('i', target)
                    .removeClass('fa-caret-up')
                    .addClass('fa-caret-down');
                $('span', target).html('more');
            }
        });

        /**
         * Search table check all
         */
        $(window).on('table-checkall-init', function(e, trigger) {
            var target = $(trigger).parents('table').eq(0);

            $(trigger).click(function() {
                if($(trigger).prop('checked')) {
                    $('td input[type="checkbox"]', target).prop('checked', true);
                } else {
                    $('td input[type="checkbox"]', target).prop('checked', false);
                }
            });

            $('td input[type="checkbox"]', target).click(function() {
                var allChecked = true;
                $('td input[type="checkbox"]', target).each(function() {
                    if(!$(this).prop('checked')) {
                        allChecked = false;
                    }
                });

                $(trigger).prop('checked', allChecked);
            });
        });

        /**
         * Search table check all
         */
        $(window).on('show-notif-init', function(e, trigger) {
            var target = $(trigger);

            $.ajax({
                url: '/ajax/admin/history/get/notification',
                type: 'post',
                // form data
                data: {},
                // disable cache
                cache: false,
                // do not set content type
                contentType: false,
                // do not proccess data
                processData: false,
                // on error
                error: function(xhr, status, message) {
                },
                // on success
                success : function(res) {
                    res = eval('('+res+')');

                    var rows = res.results.rows;
                    rows.forEach(function(log, key) {
                        $('div.dropdown-menu #logs').append('<a class="dropdown-item" href="#">' +
                                '<img class="rounded-circle" src="/images/default-avatar.png" />' +
                                '<span class="notification-info">' +
                                    '<span class="notification-message">' + log.history_activity + '</span>' +
                                    '<em class="notification-time">' + log.ago + '</em>' +
                                '</span>' +
                            '</a>'
                        );

                        if (key == 4) {
                            $('div.dropdown-menu #logs').addClass('notif-scroll');
                        }
                    });

                    if (rows.length === 0) {
                        $('div.dropdown-menu #logs').html('<a class="dropdown-item text-center" href="#">' +
                                '<span class="notification-info">' +
                                    '<span class="notification-message">No New Notification</span>' +
                                '</span>' +
                            '</a>'
                        );

                        //add class
                        $('.notification-info').addClass('no-notification');

                        //remove doon
                        $('a.nav-link.dropdown-toggle').removeAttr('data-do');
                        $('a.nav-link.dropdown-toggle').removeAttr('data-on');
                    }

                    $('span#notification').html(res.results.total);
                }
            });
        });

        /**
         * Search table check all
         */
        $(window).on('show-notif-click', function(e, trigger) {
            var target = $(trigger);
            $.ajax({
                url: '/ajax/admin/history/read/notification',
                type: 'post',
                // form data
                data: {},
                // disable cache
                cache: false,
                // do not set content type
                contentType: false,
                // do not proccess data
                processData: false,
                // on error
                error: function(xhr, status, message) {
                },
                // on success
                success : function(res) {
                    $('span#notification').html(0);
                }
            });
        });

        /**
         * Importer tool
         */
        $(window).on('import-click', function(e, trigger) {
            var url = $(trigger).attr('data-url');
            var schema = $(trigger).attr('data-schema');
            var relation = $(trigger).attr('data-relation');
            var relation_id = $(trigger).attr('data-relation-id');
            var non_object = $(trigger).attr('data-non-object');

            //get commplete url
            var route = '/admin/system/object/' + schema + '/' + url;

            if (typeof non_object !== 'undefined') {
                route = '/admin/' + schema + '/' + url;
            }

            url = route;

            //make a file
            $('<input type="file" />')
                .attr(
                    'accept',
                    [
                        'text/plain',
                        'text/csv',
                        'text/x-csv',
                        'application/vnd.ms-excel',
                        'application/csv',
                        'application/x-csv',
                        'text/comma-separated-values',
                        'text/x-comma-separated-values',
                        'text/tab-separated-values'
                    ].join(',')
                )
                .change(function() {
                    $(this).parse({
                        config: {
                            header: true,
                            skipEmptyLines: true,
                            complete: function(results, file) {
                                var form = $('<form>')
                                    .attr('method', 'post')
                                    .attr('action', url);

                                results.data.forEach(function(row, i) {
                                    var key, name;
                                    for(key in row) {
                                        name = 'rows[' + i + '][' + key + ']';
                                        $('<input>')
                                            .attr('type', 'hidden')
                                            .attr('name', name)
                                            .attr('value', row[key])
                                            .appendTo(form);
                                    }
                                });

                                //if relation exists
                                if (typeof relation !== 'undefined' && typeof relation_id !== 'undefined') {
                                    var relationField = 'relation[' + relation + ']';

                                    $('<input>')
                                        .attr('type', 'hidden')
                                        .attr('name', relationField)
                                        .attr('value', relation_id)
                                        .appendTo(form);
                                }

                                form.hide().appendTo(document.body).submit();
                            },
                            error: function(error, file, input, reason) {
                                $.notify(error.message, 'error');
                            }
                        }
                    });
                })
                .click();
        });
    })();

    /**
     * General Forms
     */
    (function() {
        /**
         * Suggestion Field
         */
        $(window).on('suggestion-field-init', function(e, target) {
            target = $(target);

            var container = $('<ul>').appendTo(target);

            var searching = false,
                prevent = false,
                value = target.attr('data-value'),
                format = target.attr('data-format'),
                targetLabel = target.attr('data-target-label'),
                targetValue = target.attr('data-target-value'),
                url = target.attr('data-url')
                template = '<li class="suggestion-item">{VALUE}</li>';

            if(!targetLabel || !targetValue || !url || !value) {
                return;
            }

            targetLabel = $(targetLabel);
            targetValue = $(targetValue);

            var loadSuggestions = function(list, callback) {
                container.html('');

                list.forEach(function(item) {
                    var label = '';
                    //if there is a format, yay.
                    if (format) {
                        label = Handlebars.compile(format)(item);
                    //otherwise best guess?
                    } else {
                        for (var key in item) {
                            if(
                                //if it is not a string
                                typeof item[key] !== 'string'
                                //it's a string but is like a number
                                || !isNaN(parseFloat(item[key]))
                                //it's a string and is not like a number
                                // but the first character is like a number
                                || !isNaN(parseFloat(item[key][0]))
                            ) {
                                continue;
                            }

                            label = item[key];
                        }
                    }

                    //if still no label
                    if(!label.length) {
                        //just get the first one, i guess.
                        for (var key in item) {
                            label = item[key];
                            break;
                        }
                    }

                    item = { label: label, value: item[value] };
                    var row = template.replace('{VALUE}', item.label);

                    row = $(row).click(function() {
                        callback(item);
                        target.addClass('d-none');
                    });

                    container.append(row);
                });

                if(list.length) {
                    target.removeClass('d-none');
                } else {
                    target.addClass('d-none');
                }
            };

            targetLabel
                .keypress(function(e) {
                    //if enter
                    if(e.keyCode == 13 && prevent) {
                        e.preventDefault();
                    }
                })
                .keydown(function(e) {
                    //if backspace
                    if(e.keyCode == 8) {
                        //undo the value
                        targetValue.val('');
                    }

                    prevent = false;
                    if(!target.hasClass('d-none')) {
                        switch(e.keyCode) {
                            case 40: //down
                                var next = $('li.hover', target).removeClass('hover').index() + 1;

                                if(next === $('li', target).length) {
                                    next = 0;
                                }

                                $('li:eq('+next+')', target).addClass('hover');

                                return;
                            case 38: //up
                                var prev = $('li.hover', target).removeClass('hover').index() - 1;

                                if(prev < 0) {
                                    prev = $('li', target).length - 1;
                                }

                                $('li:eq('+prev+')', target).addClass('hover');

                                return;
                            case 13: //enter
                                if($('li.hover', target).length) {
                                    $('li.hover', target)[0].click();
                                    prevent = true;
                                }
                                return;
                            case 37:
                            case 39:
                                return;
                        }
                    }

                    if(searching) {
                        return;
                    }

                    setTimeout(function() {
                        if (targetLabel.val() == '') {
                            return;
                        }

                        searching = true;
                        $.ajax({
                            url : url.replace('{QUERY}', targetLabel.val()),
                            type : 'GET',
                            success : function(response) {
                                var list = [];

                                if(typeof response.results !== 'undefined'
                                    && typeof response.results.rows !== 'undefined'
                                    && response.results.rows instanceof Array
                                ) {
                                    list = response.results.rows;
                                }

                                loadSuggestions(list, function(item) {
                                    targetValue.val(item.value);
                                    targetLabel.val(item.label).trigger('keyup');
                                });

                                searching = false;
                            }, error : function() {
                                searching = false;
                            }
                        });
                    }, 1);
                });
        });

        /**
         * Tag Field
         */
        $(window).on('tag-field-init', function(e, target) {
            target = $(target);

            var name = target.attr('data-name');

            //TEMPLATES
            var tagTemplate = '<div class="tag"><input type="text" class="tag-input'
            + ' text-field" name="' + name + '[]" placeholder="Tag" value="" />'
            + '<a class="remove" href="javascript:void(0)"><i class="fa fa-times">'
            + '</i></a></div>';

            var addResize = function(filter) {
                var input = $('input[type=text]', filter);

                input.keyup(function() {
                    var value = input.val() || input.attr('placeholder');

                    var test = $('<span>').append(value).css({
                        visibility: 'hidden',
                        position: 'absolute',
                        top: 0, left: 0
                    }).appendTo('header:first');

                    var width = test.width() + 10;

                    if((width + 40) > target.width()) {
                        width = target.width() - 40;
                    }

                    $(this).width(width);
                    test.remove();
                }).trigger('keyup');
            };

            var addRemove = function(filter) {
                $('a.remove', filter).click(function() {
                    var val = $('input', filter).val();

                    $(this).parent().remove();
                });
            };

            //INITITALIZERS
            var initTag = function(filter) {
                addRemove(filter);
                addResize(filter);

                $('input', filter).blur(function() {
                    //if no value
                    if(!$(this).val() || !$(this).val().length) {
                        //remove it
                        $(this).next().click();
                    }

                    var count = 0;
                    var currentTagValue = $(this).val();
                    $('div.tag input', target).each(function() {
                        if(currentTagValue === $(this).val()) {
                            count++;
                        }
                    });

                    if(count > 1) {
                        $(this).parent().remove();
                    }
                });
            };

            //EVENTS
            target.click(function(e) {
                if($(e.target).hasClass('tag-field')) {
                    var last = $('div.tag:last', this);

                    if(!last.length || $('input', last).val()) {
                        last = $(tagTemplate);
                        target.append(last);

                        initTag(last);
                    }

                    $('input', last).focus();
                }
            });

            //INITIALIZE
            $('div.tag', target).each(function() {
                initTag($(this));
            });
        });

        /**
         * Meta Field
         */
        $(window).on('meta-field-init', function(e, target) {
            target = $(target);

            //TEMPLATES
            var metaTemplate ='<div class="meta">'
                + '<input type="text" class="meta-input key" /> '
                + '<input type="text" class="meta-input value" /> '
                + '<input type="hidden" name="post_tags[{{@key}}]" value=""/> '
                + '<a class="remove" href="javascript:void(0)"><i class="fa fa-times"></i></a>'
                + '</div>';


            var addRemove = function(filter) {
                $('a.remove', filter).click(function() {
                    var val = $('input', filter).val();

                    $(this).parent().remove();
                });
            };

            //INITITALIZERS
            var initTag = function(filter) {
                addRemove(filter);

                $('.meta-input.key', filter).blur(function() {
                    var hidden = $(this).parent().find('input[type="hidden"]');

                    //if no value
                    if(!$(this).val() || !$(this).val().length) {
                        $(hidden).attr('name', '');
                        return;
                    }

                    $(hidden).attr('name', $(target).data('name') + '[' + $(this).val() +']');
                });

                $('.meta-input.value', filter).blur(function() {
                    var hidden = $(this).parent().find('input[type="hidden"]');

                    //if no value
                    if(!$(this).val() || !$(this).val().length) {
                        $(hidden).attr('name', '');
                        return;
                    }

                    $(hidden).attr('value', $(this).val());
                });
            };

            //append meta template
            $('.add-meta').click(function() {
                var last = $('div.meta:last', target);
                if(!last.length || $('input', last).val()) {
                    target.append(metaTemplate);
                    initTag(target);
                }

                return false;
            });

            //INITIALIZE
            $('div.meta', target).each(function() {
                initTag($(this));
            });
        });

        /**
         * File Field
         * HTML config for single files
         * data-do="file-field"
         * data-name="post_files"
         *
         * HTML config for multiple files
         * data-do="file-field"
         * data-name="post_files"
         * data-multiple="1"
         */
        $(window).on('file-field-init', function(e, target) {
            var template = {
                previewFile:
                    '<div class="file-field-preview-container">'
                    + '<i class="fas fa-file text-info"></i>'
                    + '<span class="file-field-extension">{EXTENSION}</span>'
                    + '</div>',
                previewImage:
                    '<div class="file-field-preview-container">'
                    + '<img src="{DATA}" height="50" />'
                    + '</div>',
                actions:
                    '<td class="file-field-actions">'
                        + '<a class="text-info file-field-move-up" href="javascript:void(0)">'
                            + '<i class="fas fa-arrow-up"></i>'
                        + '</a>'
                        + '&nbsp;&nbsp;&nbsp;'
                        + '<a class="text-info file-field-move-down" href="javascript:void(0)">'
                            + '<i class="fas fa-arrow-down"></i>'
                        + '</a>'
                        + '&nbsp;&nbsp;&nbsp;'
                        + '<a class="btn btn-danger file-field-remove" href="javascript:void(0)">'
                            + '<i class="fas fa-times"></i>'
                        + '</a>'
                    + '</td>',
                row:
                    '<tr class="file-field-item">'
                    + '<td class="file-field-preview">{PREVIEW}</td>'
                    + '<td class="file-field-name">'
                        + '{FILENAME}'
                        + '<input name="{NAME}" type="hidden" value="{DATA}" />'
                    + '</td>'
                    + '{ACTIONS}'
                    + '</tr>'
            };

            //current
            var container = $(target);
            var body = $('tbody', container);
            var foot = $('tfoot', container);

            var noresults = $('tr.file-field-none', body);

            //get meta data

            //for hidden fields
            var name = container.attr('data-name');

            //for file field
            var multiple = container.attr('data-multiple');
            var accept = container.attr('data-accept') || false;
            var classes = container.attr('data-class');
            var width = parseInt(container.attr('data-width') || 0);
            var height = parseInt(container.attr('data-height') || 0);

            //make a file
            var file = $('<input type="file" />').hide();

            if(multiple) {
                file.attr('multiple', 'multiple');
            }

            if(accept) {
                file.attr('accept', accept);
            }

            foot.append(file);

            $('button.file-field-upload', container).click(function(e) {
                file.click();
            });

            var listen = function(row, body) {
                $('a.file-field-remove', row).click(function() {
                    row.remove();
                    if($('tr', body).length < 2) {
                        noresults.show();
                    }
                });

                $('a.file-field-move-up', row).click(function() {
                    var prev = row.prev();

                    if(prev.length && !prev.hasClass('file-field-none')) {
                        prev.before(row);
                    }
                });

                $('a.file-field-move-down', row).click(function() {
                    var next = row.next();

                    if(next.length) {
                        next.after(row);
                    }
                });
            };

            var generate = function(file, name, width, height, row) {
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function () {
                    var extension = file.name.split('.').pop();

                    if(file.name.indexOf('.') === -1) {
                        extension = 'unknown';
                    }

                    var preview = template.previewFile.replace('{EXTENSION}', extension);

                    if(file.type.indexOf('image/') === 0) {
                        preview = template.previewImage.replace('{DATA}', reader.result);
                    }

                    noresults.hide();

                    row = $(
                        row
                            .replace('{NAME}', name)
                            .replace('{DATA}', reader.result)
                            .replace('{PREVIEW}', preview)
                            .replace('{FILENAME}', file.name)
                    ).appendTo(body);

                    listen(row, body);

                    if(file.type.indexOf('image/') === 0 && (width !== 0 || height !== 0)) {
                        //so we can crop
                        $.cropper(file, width, height, function(data) {
                            $('div.file-field-preview-container img', row).attr('src', data);
                            $('input[type="hidden"]', row).val(data);
                        });
                    }

                    //add mime type
                    if(typeof mimeExtensions[file.type] !== 'string') {
                        mimeExtensions[file.type] = extension;
                    }
                };
            };

            file.change(function() {
                if(!this.files || !this.files[0]) {
                    return;
                }

                if(!multiple) {
                    $('tr', body).each(function() {
                        if($(this).hasClass('file-field-none')) {
                            return;
                        }

                        $(this).remove();
                    })
                }

                for(var row, path = '', i = 0; i < this.files.length; i++, path = '') {
                    row = template.row.replace('{ACTIONS}', '');
                    if(multiple) {
                        path = '[]' + path;
                        row = template.row.replace('{ACTIONS}', template.actions);
                    }

                    path = name + path;
                    generate(this.files[i], path, width, height, row);
                }
            });

            $('tr', body).each(function() {
                if($(this).hasClass('file-field-none')) {
                    return;
                }

                listen($(this), body)
            });
        });

        /**
         * Direct CDN Upload
         */
        $(window).on('wysiwyg-init', function(e, target) {
            var template = '<div class="wysiwyg-toolbar position-relative" style="display: none;">'
                + '<div class="btn-group">'
                    + '<a class="btn btn-default" data-wysihtml-command="bold" title="CTRL+B"><i class="fas fa-bold"></i></a>'
                    + '<a class="btn btn-default" data-wysihtml-command="italic" title="CTRL+I"><i class="fas fa-italic"></i></a>'
                    + '<a class="btn btn-default" data-wysihtml-command="underline" title="CTRL+U"><i class="fas fa-underline"></i></a>'
                    + '<a class="btn btn-default" data-wysihtml-command="strike" title="CTRL+U"><i class="fas fa-strikethrough"></i></a>'
                + '</div> '
                + '<div class="btn-group">'
                    + '<a class="btn btn-info" data-wysihtml-command="createLink"><i class="fas fa-external-link-alt"></i></a>'
                    + '<a class="btn btn-danger" data-wysihtml-command="removeLink"><i class="fas fa-ban"></i></a>'
                + '</div> '
                + '<a class="btn btn-purple" data-wysihtml-command="insertImage"><i class="fas fa-image"></i></a> '
                + '<div class="dropdown d-inline-block">'
                    + '<button aria-haspopup="true" aria-expanded="false" class="btn btn-grey" data-toggle="dropdown" type="button">Headers <i class="fas fa-chevron-down"></i></button>'
                    + '<div class="dropdown-menu">'
                        + '<a class="dropdown-item" data-wysihtml-command="formatBlock" data-wysihtml-command-blank-value="true">Normal</a>'
                        + '<a class="dropdown-item" data-wysihtml-command="formatBlock" data-wysihtml-command-value="h1">Header 1</a>'
                        + '<a class="dropdown-item" data-wysihtml-command="formatBlock" data-wysihtml-command-value="h2">Header 2</a>'
                        + '<a class="dropdown-item" data-wysihtml-command="formatBlock" data-wysihtml-command-value="h3">Header 3</a>'
                        + '<a class="dropdown-item" data-wysihtml-command="formatBlock" data-wysihtml-command-value="h4">Header 4</a>'
                        + '<a class="dropdown-item" data-wysihtml-command="formatBlock" data-wysihtml-command-value="h5">Header 5</a>'
                        + '<a class="dropdown-item" data-wysihtml-command="formatBlock" data-wysihtml-command-value="h6">Header 6</a>'
                    + '</div>'
                + '</div> '
                + '<div class="dropdown d-inline-block">'
                    + '<button aria-haspopup="true" aria-expanded="false" class="btn btn-pink" data-toggle="dropdown" type="button">Colors <i class="fas fa-chevron-down"></i></button>'
                    + '<div class="dropdown-menu">'
                        + '<a class="dropdown-item text-danger" data-wysihtml-command="foreColor" data-wysihtml-command-value="red"><i class="fas fa-square-full"></i> Red</a>'
                        + '<a class="dropdown-item text-success" data-wysihtml-command="foreColor" data-wysihtml-command-value="green"><i class="fas fa-square-full"></i> Green</a>'
                        + '<a class="dropdown-item text-primary" data-wysihtml-command="foreColor" data-wysihtml-command-value="blue"><i class="fas fa-square-full"></i> Blue</a>'
                        + '<a class="dropdown-item text-purple" data-wysihtml-command="foreColor" data-wysihtml-command-value="purple"><i class="fas fa-square-full"></i> Purple</a>'
                        + '<a class="dropdown-item text-warning" data-wysihtml-command="foreColor" data-wysihtml-command-value="orange"><i class="fas fa-square-full"></i> Orange</a>'
                        + '<a class="dropdown-item text-yellow" data-wysihtml-command="foreColor" data-wysihtml-command-value="yellow"><i class="fas fa-square-full"></i> Yellow</a>'
                        + '<a class="dropdown-item text-pink" data-wysihtml-command="foreColor" data-wysihtml-command-value="pink"><i class="fas fa-square-full"></i> Pink</a>'
                        + '<a class="dropdown-item text-white" data-wysihtml-command="foreColor" data-wysihtml-command-value="white"><i class="fas fa-square-full"></i> White</a>'
                        + '<a class="dropdown-item text-inverse" data-wysihtml-command="foreColor" data-wysihtml-command-value="black"><i class="fas fa-square-full"></i> Black</a>'
                    + '</div>'
                + '</div> '
                + '<div class="btn-group">'
                    + '<a class="btn btn-default" data-wysihtml-command="insertUnorderedList"><i class="fas fa-list-ul"></i></a>'
                    + '<a class="btn btn-default" data-wysihtml-command="insertOrderedList"><i class="fas fa-list-ol"></i></a>'
                + '</div> '
                + '<div class="btn-group">'
                    + '<a class="btn btn-light" data-wysihtml-command="undo"><i class="fas fa-undo"></i></a><a class="btn btn-light" data-wysihtml-command="redo"><i class="fas fa-redo"></i></a>'
                + '</div> '
                + '<a class="btn btn-light" data-wysihtml-command="insertSpeech"><i class="fas fa-comments"></i></a> '
                + '<a class="btn btn-inverse" data-wysihtml-action="change_view"><i class="fas fa-code"></i></a> '
                + '<div class="wysiwyg-dialog" data-wysihtml-dialog="createLink" style="display: none;">'
                    + '<input class="form-control" data-wysihtml-dialog-field="href" placeholder="http://" />'
                    + '<input class="form-control mb-2" data-wysihtml-dialog-field="title" placeholder="Title" />'
                    + '<a class="btn btn-primary" data-wysihtml-dialog-action="save" href="javascript:void(0)">OK</a>'
                    + '<a class="btn btn-danger" data-wysihtml-dialog-action="cancel" href="javascript:void(0)">Cancel</a>'
                + '</div>'
                + '<div class="wysiwyg-dialog" data-wysihtml-dialog="insertImage" style="display: none;">'
                    + '<input class="form-control" data-wysihtml-dialog-field="src" placeholder="http://">'
                    + '<input class="form-control" data-wysihtml-dialog-field="alt" placeholder="alt">'
                    + '<select class="form-control mb-2" data-wysihtml-dialog-field="className">'
                        + '<option value="">None</option>'
                        + '<option value="float-left">Left</option>'
                        + '<option value="float-right">Right</option>'
                    + '</select>'
                    + '<a class="btn btn-primary" data-wysihtml-dialog-action="save" href="javascript:void(0)">OK</a>'
                    + '<a class="btn btn-danger" data-wysihtml-dialog-action="cancel" href="javascript:void(0)">Cancel</a>'
                + '</div>'
            + '</div>';

            var toolbar = $(template);
            $(target).before(toolbar);

            var e = new wysihtml.Editor(target, {
                toolbar:        toolbar[0],
                parserRules:    wysihtmlParserRules,
                stylesheets:  '/styles/admin.css'
            });
        });

        /**
         * Generate Slug
         */
        $(window).on('slugger-init', function(e, target) {
            var source = $(target).attr('data-source');

            if(!source || !source.length) {
                return;
            }

            var upper = $(target).attr('data-upper');
            var space = $(target).attr('data-space') || '-';

            $(source).keyup(function() {
                var slug = $(this)
                    .val()
                    .toString()
                    .toLowerCase()
                    .replace(/\s+/g, '-')           // Replace spaces with -
                    .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                    .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                    .replace(/^-+/, '')             // Trim - from start of text
                    .replace(/-+$/, '');

                if (upper != 0) {
                    slug = slug.replace(
                        /(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g,
                        function(s) {
                            return s.toUpperCase();
                        }
                    );
                }

                slug = slug.replace(/\-/g, space);

                $(target).val(slug);
            });
        });

        /**
         * Mask
         */
        $(window).on('mask-field-init', function(e, target) {
            var format = $(target).attr('data-format');
            $(target).inputmask(format);
        });

        /**
         * Mask
         */
        $(window).on('knob-field-init', function(e, target) {
            $(target).knob();
        });

        /**
         * Select
         */
        $(window).on('select-field-init', function(e, target) {
            $(target).select2();
        });

        /**
         * Code Editor - Ace
         */
        $(window).on('code-editor-init', function(e, target) {
            target = $(target);

            var editor = ace.edit("editor");

            editor.getSession().setMode("ace/mode/html");
            editor.setTheme("ace/theme/chaos");
            editor.setValue('<!DOCTYPE html> \n'+
                '<html> \n'+
                '\t<head> \n'+
                '\t\t<title>Ace Editor</title> \n'+
                '\t</head> \n'+
                '\t<body> \n'+
                '\t</body> \n'+
                '</html>');
            editor.getValue();

            setInterval(function() {
                $('#editor-raw').val(editor.getValue());
            }, 200);
        });

        /**
         * Multirange
         */
        $(window).on('multirange-field-init', function(e, target) {
            target = $(target);

            var params = {};
            // loop all attributes
            $.each(target[0].attributes,function(index, attr) {
                // skip if data do and on
                if (attr.name == 'data-do' || attr.name == 'data-on') {
                    return true;
                }

                // look for attr with data- as prefix
                if (attr.name.search(/data-/g) > -1) {
                    // get parameter name
                    var key = attr.name
                        .replace('data-', '')
                        .replace('-', '_');

                    // prepare parameter
                    params[key] = attr.value;

                    // if value is boolean
                    if(attr.value == 'true') {
                        params[key] = attr.value == 'true' ? true : false;
                    }
                }
            });

            target.ionRangeSlider(params);
        });

        /**
         * Date Field
         */
        $(window).on('date-field-init', function(e, target) {
            $(target).flatpickr({
                dateFormat: "Y-m-d",
            });
        });

        /**
         * Time Field
         */
        $(window).on('time-field-init', function(e, target) {
            $(target).flatpickr({
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
            });
        });

        /**
         * DateTime Field
         */
        $(window).on('datetime-field-init', function(e, target) {
            $(target).flatpickr({
                enableTime: true,
                dateFormat: "Y-m-d H:i",
            });
        });

        /**
         * Date Range Field
         */
        $(window).on('date-range-field-init', function(e, target) {
            $(target).flatpickr({
                mode: "range",
                dateFormat: "Y-m-d",
            });
        });

        /**
         * DateTime Range Field
         */
        $(window).on('datetime-range-field-init', function(e, target) {
            $(target).flatpickr({
                mode: "range",
                enableTime: true,
                dateFormat: "Y-m-d H:i",
            });
        });

        /**
         * Icon field
         */
        $(window).on('icon-field-init', function(e, target) {
            var target = $(target);

            var targetLevel = parseInt(target.attr('data-target-parent')) || 0;

            var suggestion = $('<div>')
                .addClass('input-suggestion')
                .addClass('icon-field')
                .hide();

            var parent = target;
            for(var i = 0; i < targetLevel; i++) {
                parent = parent.parent();
            }

            parent.after(suggestion);

            target.click(function() {
                    suggestion.show();
                })
                .blur(function() {
                    setTimeout(function() {
                        suggestion.hide();
                    }, 100);
                });

            icons.forEach(function(icon) {
                $('<i>')
                    .addClass(icon)
                    .addClass('fa-fw')
                    .appendTo(suggestion)
                    .click(function() {
                        var input = target.parent().find('input').eq(0);
                        input.val(this.className.replace(' fa-fw', ''));

                        var preview = target.parent().find('i').eq(0);
                        if(!preview.parent().hasClass('icon-suggestion')) {
                            preview[0].className = this.className;
                        }

                        suggestion.hide();
                        target.focus();
                    });
            });

            $('i', target.attr('data-target'));
        });

        /**
         * Object Range Change
         */
        $(window).on('object-range-change', function(e, target) {
            var target = $(target);

            var form = $('<form>')
                .attr('method', 'get');

            //if relation exists
            if (typeof target.val() !== 'undefined' && target.val() !== '') {
                $('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'range')
                    .attr('value', target.val())
                    .appendTo(form);
            }

            form.hide().appendTo(document.body).submit();
        });

        /**
         * Direct CDN Upload
         */
        $(window).on('cdn-upload-submit', function(e, target) {
            //setup cdn configuration
            var container = $(target);
            var config = { form: {}, inputs: {} };

            //though we upload this with s3 you may be using cloudfront
            config.cdn = container.attr('data-cdn');
            config.progress = container.attr('data-progress');
            config.complete = container.attr('data-complete');

            //form configuration
            config.form['enctype'] = container.attr('data-enctype');
            config.form['method'] = container.attr('data-method');
            config.form['action'] = container.attr('data-action');

            //inputs configuration
            config.inputs['acl'] = container.attr('data-acl');
            config.inputs['key'] = container.attr('data-key');
            config.inputs['X-Amz-Credential'] = container.attr('data-credential');
            config.inputs['X-Amz-Algorithm'] = container.attr('data-algorythm');
            config.inputs['X-Amz-Date'] = container.attr('data-date');
            config.inputs['Policy'] = container.attr('data-policy');
            config.inputs['X-Amz-Signature'] = container.attr('data-signature');

            var id = 0,
                // /upload/123abc for example
                prefix = config.inputs.key,
                //the total of files to be uploaded
                total = 0,
                //the amount of uploads complete
                completed = 0;

            //hiddens will have base 64
            $('input[type="hidden"]', target).each(function() {
                var hidden = $(this);
                var data = hidden.val();
                //check for base 64
                if(data.indexOf(';base64,') === -1) {
                    return;
                }

                //parse out the base 64 so we can make a file
                var base64 = data.split(';base64,');
                var mime = base64[0].split(':')[1];

                var extension = mimeExtensions[mime] || 'unknown';
                //this is what hidden will be assigned to when it's uploaded
                var path = prefix + (++id) + '.' + extension;

                //EPIC: Base64 to File Object
                var byteCharacters = window.atob(base64[1]);
                var byteArrays = [];

                for (var offset = 0; offset < byteCharacters.length; offset += 512) {
                    var slice = byteCharacters.slice(offset, offset + 512);

                    var byteNumbers = new Array(slice.length);

                    for (var i = 0; i < slice.length; i++) {
                        byteNumbers[i] = slice.charCodeAt(i);
                    }

                    var byteArray = new Uint8Array(byteNumbers);

                    byteArrays.push(byteArray);
                }

                var file = new File(byteArrays, {type: mime});

                //This Code is to verify that we are
                //encoding the file data correctly
                //see: http://stackoverflow.com/questions/16245767/creating-a-blob-from-a-base64-string-in-javascript
                //var reader  = new FileReader();
                //var preview = $('<img>').appendTo(target)[0];
                //reader.addEventListener("load", function () {
                //    preview.src = reader.result;
                //}, false);
                //reader.readAsDataURL(file);
                //return;

                //add on to the total
                total ++;

                //prepare the S3 form to upload just this file
                var form = new FormData();
                for(var name in config.inputs) {
                    if(name === 'key') {
                        form.append('key', path);
                        continue;
                    }

                    form.append(name, config.inputs[name]);
                }

                //lastly add this file object
                form.append('file', file);

                // Need to use jquery ajax
                // so that auth can catch
                // up request, and append access
                // token into it
                $.ajax({
                    url: config.form.action,
                    type: config.form.method,
                    // form data
                    data: form,
                    // disable cache
                    cache: false,
                    // do not set content type
                    contentType: false,
                    // do not proccess data
                    processData: false,
                    // on error
                    error: function(xhr, status, message) {
                        notifier.fadeOut('fast', function() {
                            notifier.remove();
                        });

                        $.notify(message, 'danger');
                    },
                    // on success
                    success : function() {
                        //now we can reassign hidden value from
                        //base64 to CDN Link
                        hidden.val(config.cdn + '/' + path);

                        //if there is more to upload
                        if ((++completed) < total) {
                            //update bar
                            var percent = Math.floor((completed / total) * 100);
                            bar.css('width', percent + '%').html(percent + '%');

                            //do nothing else
                            return;
                        }

                        notifier.fadeOut('fast', function() {
                            notifier.remove();
                        });

                        $.notify(config.complete, 'success');

                        //all hidden fields that could have possibly
                        //been converted has been converted
                        //submit the form
                        target.submit();
                    }
                });
            });

            //if there is nothing to upload
            if(!total) {
                //let the form submit as normal
                return;
            }

            //otherwise we are uploading something, so we need to wait
            e.preventDefault();

            var message = '<div>' + config.progress + '</div>';
            var progress = '<div class="progress"><div class="progress-bar"'
            + 'role="progressbar" aria-valuenow="2" aria-valuemin="0"'
            + 'aria-valuemax="100" style="min-width: 2em; width: 0%;">0%</div></div>';

            var notifier = $.notify(message + progress, 'info', 0);
            var bar = $('div.progress-bar', notifier);
        });

        var mimeExtensions = {
            'application/mathml+xml': 'mathml',
            'application/msword': 'doc',
            'application/oda': 'oda',
            'application/ogg': 'ogg',
            'application/pdf': 'pdf',
            'application/rdf+xml': 'rdf',
            'application/vnd.mif': 'mif',
            'application/vnd.mozilla.xul+xml': 'xul',
            'application/vnd.ms-excel': 'xls',
            'application/vnd.ms-powerpoint': 'ppt',
            'application/vnd.rn-realmedia': 'rm',
            'application/vnd.wap.wbxml': 'wbmxl',
            'application/vnd.wap.wmlc': 'wmlc',
            'application/vnd.wap.wmlscriptc': 'wmlsc',
            'application/vnd.ms-word.document.macroEnabled.12': 'docm',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'docx',
            'application/vnd.ms-word.template.macroEnabled.12': 'dotm',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.template': 'dotx',
            'application/vnd.ms-powerpoint.slideshow.macroEnabled.12': 'ppsm',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow': 'ppsx',
            'application/vnd.ms-powerpoint.presentation.macroEnabled.12': 'pptm',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'pptx',
            'application/vnd.ms-excel.sheet.binary.macroEnabled.12': 'xlsb',
            'application/vnd.ms-excel.sheet.macroEnabled.12': 'xlsm',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'xslx',
            'application/vnd.ms-xpsdocument': 'xps',
            'application/voicexml+xml': 'vxml',
            'application/x-javascript': 'js',
            'application/x-shockwave-flash': 'swf',
            'application/x-tar': 'tar',
            'application/xhtml+xml': 'xhtml',
            'application/xml': 'xml',
            'application/xml-dtd': 'dtd',
            'application/xslt+xml': 'xslt',
            'application/zip': 'zip',
            'audio/basic': 'snd',
            'audio/midi': 'midi',
            'audio/mp4a-latm': 'm4p',
            'audio/mpeg': 'mpga',
            'audio/x-aiff': 'aiff',
            'audio/x-mpegurl': 'm3u',
            'audio/x-pn-realaudio': 'ram',
            'audio/x-wav': 'wav',
            'image/bmp': 'bmp',
            'image/cgm': 'cgm',
            'image/gif': 'gif',
            'image/ief': 'ief',
            'image/jp2': 'jp2',
            'image/jpg': 'jpg',
            'image/jpeg': 'jpg',
            'image/pict': 'pict',
            'image/png': 'png',
            'image/svg+xml': 'svg',
            'image/tiff': 'tiff',
            'image/vnd.djvu': 'djvu',
            'image/vnd.wap.wbmp': 'wbmp',
            'image/x-cmu-raster': 'ras',
            'image/x-icon': 'ico',
            'image/x-macpaint': 'pntg',
            'image/x-portable-anymap': 'pnm',
            'image/x-portable-bitmap': 'pbm',
            'image/x-portable-graymap': 'pgm',
            'image/x-portable-pixmap': 'ppm',
            'image/x-quicktime': 'qtif',
            'image/x-rgb': 'rgb',
            'image/x-xbitmap': 'xbm',
            'image/x-xpixmap': 'xpm',
            'image/x-xwindowdump': 'xwd',
            'model/iges': 'igs',
            'model/mesh': 'silo',
            'model/vrml': 'wrl',
            'text/calendar': 'ifb',
            'text/css': 'css',
            'text/html': 'html',
            'text/plain': 'txt',
            'text/richtext': 'rtx',
            'text/rtf': 'rtf',
            'text/sgml': 'sgml',
            'text/tab-separated-values': 'tsv',
            'text/vnd.wap.wml': 'wml',
            'text/vnd.wap.wmlscript': 'wmls',
            'text/x-setext': 'etx',
            'video/mp4': 'mp4',
            'video/mpeg': 'mpg',
            'video/quicktime': 'qt',
            'video/vnd.mpegurl': 'mxu',
            'video/x-dv': 'dv',
            'video/x-m4v': 'm4v',
            'video/x-msvideo': 'avi',
            'video/x-sgi-movie': 'movie'
        };

        var icons = [
            'fas fa-address-book',
            'fas fa-address-card',
            'fas fa-adjust',
            'fas fa-align-center',
            'fas fa-align-justify',
            'fas fa-align-left',
            'fas fa-align-right',
            'fas fa-ambulance',
            'fas fa-american-sign-language-interpreting',
            'fas fa-anchor',
            'fas fa-angle-double-down',
            'fas fa-angle-double-left',
            'fas fa-angle-double-right',
            'fas fa-angle-double-up',
            'fas fa-angle-down',
            'fas fa-angle-left',
            'fas fa-angle-right',
            'fas fa-angle-up',
            'fas fa-archive',
            'fas fa-arrow-alt-circle-down',
            'fas fa-arrow-alt-circle-left',
            'fas fa-arrow-alt-circle-right',
            'fas fa-arrow-alt-circle-up',
            'fas fa-arrow-circle-down',
            'fas fa-arrow-circle-left',
            'fas fa-arrow-circle-right',
            'fas fa-arrow-circle-up',
            'fas fa-arrow-down',
            'fas fa-arrow-left',
            'fas fa-arrow-right',
            'fas fa-arrow-up',
            'fas fa-arrows-alt',
            'fas fa-arrows-alt-h',
            'fas fa-arrows-alt-v',
            'fas fa-assistive-listening-systems',
            'fas fa-asterisk',
            'fas fa-at',
            'fas fa-audio-description',
            'fas fa-backward',
            'fas fa-balance-scale',
            'fas fa-ban',
            'fas fa-barcode',
            'fas fa-bars',
            'fas fa-bath',
            'fas fa-battery-empty',
            'fas fa-battery-full',
            'fas fa-battery-half',
            'fas fa-battery-quarter',
            'fas fa-battery-three-quarters',
            'fas fa-bed',
            'fas fa-beer',
            'fas fa-bell',
            'fas fa-bell-slash',
            'fas fa-bicycle',
            'fas fa-binoculars',
            'fas fa-birthday-cake',
            'fas fa-blind',
            'fas fa-bold',
            'fas fa-bolt',
            'fas fa-bomb',
            'fas fa-book',
            'fas fa-bookmark',
            'fas fa-bowling-ball',
            'fas fa-braille',
            'fas fa-briefcase',
            'fas fa-bug',
            'fas fa-building',
            'fas fa-bullhorn',
            'fas fa-bullseye',
            'fas fa-bus',
            'fas fa-calculator',
            'fas fa-calendar',
            'fas fa-calendar-alt',
            'fas fa-calendar-check',
            'fas fa-calendar-minus',
            'fas fa-calendar-plus',
            'fas fa-calendar-times',
            'fas fa-camera',
            'fas fa-camera-retro',
            'fas fa-car',
            'fas fa-caret-down',
            'fas fa-caret-left',
            'fas fa-caret-right',
            'fas fa-caret-square-down',
            'fas fa-caret-square-left',
            'fas fa-caret-square-right',
            'fas fa-caret-square-up',
            'fas fa-caret-up',
            'fas fa-cart-arrow-down',
            'fas fa-cart-plus',
            'fas fa-certificate',
            'fas fa-chart-area',
            'fas fa-chart-bar',
            'fas fa-chart-line',
            'fas fa-chart-pie',
            'fas fa-check',
            'fas fa-check-circle',
            'fas fa-check-square',
            'fas fa-chevron-circle-down',
            'fas fa-chevron-circle-left',
            'fas fa-chevron-circle-right',
            'fas fa-chevron-circle-up',
            'fas fa-chevron-down',
            'fas fa-chevron-left',
            'fas fa-chevron-right',
            'fas fa-chevron-up',
            'fas fa-child',
            'fas fa-circle',
            'fas fa-circle-notch',
            'fas fa-clipboard',
            'fas fa-clock',
            'fas fa-clone',
            'fas fa-closed-captioning',
            'fas fa-cloud',
            'fas fa-cloud-download-alt',
            'fas fa-cloud-upload-alt',
            'fas fa-code',
            'fas fa-code-branch',
            'fas fa-coffee',
            'fas fa-cog',
            'fas fa-cogs',
            'fas fa-columns',
            'fas fa-comment',
            'fas fa-comment-alt',
            'fas fa-comments',
            'fas fa-compass',
            'fas fa-compress',
            'fas fa-copy',
            'fas fa-copyright',
            'fas fa-credit-card',
            'fas fa-crop',
            'fas fa-crosshairs',
            'fas fa-cube',
            'fas fa-cubes',
            'fas fa-cut',
            'fas fa-database',
            'fas fa-deaf',
            'fas fa-desktop',
            'fas fa-dollar-sign',
            'fas fa-dot-circle',
            'fas fa-download',
            'fas fa-edit',
            'fas fa-eject',
            'fas fa-ellipsis-h',
            'fas fa-ellipsis-v',
            'fas fa-envelope',
            'fas fa-envelope-open',
            'fas fa-envelope-square',
            'fas fa-eraser',
            'fas fa-euro-sign',
            'fas fa-exchange-alt',
            'fas fa-exclamation',
            'fas fa-exclamation-circle',
            'fas fa-exclamation-triangle',
            'fas fa-expand',
            'fas fa-expand-arrows-alt',
            'fas fa-external-link-alt',
            'fas fa-external-link-square-alt',
            'fas fa-eye',
            'fas fa-eye-dropper',
            'fas fa-eye-slash',
            'fas fa-fast-backward',
            'fas fa-fast-forward',
            'fas fa-fax',
            'fas fa-female',
            'fas fa-fighter-jet',
            'fas fa-file',
            'fas fa-file-alt',
            'fas fa-file-archive',
            'fas fa-file-audio',
            'fas fa-file-code',
            'fas fa-file-excel',
            'fas fa-file-image',
            'fas fa-file-pdf',
            'fas fa-file-powerpoint',
            'fas fa-file-video',
            'fas fa-file-word',
            'fas fa-film',
            'fas fa-filter',
            'fas fa-fire',
            'fas fa-fire-extinguisher',
            'fas fa-flag',
            'fas fa-flag-checkered',
            'fas fa-flask',
            'fas fa-folder',
            'fas fa-folder-open',
            'fas fa-font',
            'fas fa-forward',
            'fas fa-frown',
            'fas fa-futbol',
            'fas fa-gamepad',
            'fas fa-gavel',
            'fas fa-gem',
            'fas fa-genderless',
            'fas fa-gift',
            'fas fa-glass-martini',
            'fas fa-globe',
            'fas fa-graduation-cap',
            'fas fa-h-square',
            'fas fa-hand-lizard',
            'fas fa-hand-paper',
            'fas fa-hand-peace',
            'fas fa-hand-point-down',
            'fas fa-hand-point-left',
            'fas fa-hand-point-right',
            'fas fa-hand-point-up',
            'fas fa-hand-pointer',
            'fas fa-hand-rock',
            'fas fa-hand-scissors',
            'fas fa-hand-spock',
            'fas fa-handshake',
            'fas fa-hashtag',
            'fas fa-hdd',
            'fas fa-heading',
            'fas fa-headphones',
            'fas fa-heart',
            'fas fa-heartbeat',
            'fas fa-history',
            'fas fa-home',
            'fas fa-hospital',
            'fas fa-hourglass',
            'fas fa-hourglass-end',
            'fas fa-hourglass-half',
            'fas fa-hourglass-start',
            'fas fa-i-cursor',
            'fas fa-id-badge',
            'fas fa-id-card',
            'fas fa-image',
            'fas fa-images',
            'fas fa-inbox',
            'fas fa-indent',
            'fas fa-industry',
            'fas fa-info',
            'fas fa-info-circle',
            'fas fa-italic',
            'fas fa-key',
            'fas fa-keyboard',
            'fas fa-language',
            'fas fa-laptop',
            'fas fa-leaf',
            'fas fa-lemon',
            'fas fa-level-down-alt',
            'fas fa-level-up-alt',
            'fas fa-life-ring',
            'fas fa-lightbulb',
            'fas fa-link',
            'fas fa-lira-sign',
            'fas fa-list',
            'fas fa-list-alt',
            'fas fa-list-ol',
            'fas fa-list-ul',
            'fas fa-location-arrow',
            'fas fa-lock',
            'fas fa-lock-open',
            'fas fa-long-arrow-alt-down',
            'fas fa-long-arrow-alt-left',
            'fas fa-long-arrow-alt-right',
            'fas fa-long-arrow-alt-up',
            'fas fa-low-vision',
            'fas fa-magic',
            'fas fa-magnet',
            'fas fa-male',
            'fas fa-map',
            'fas fa-map-marker',
            'fas fa-map-marker-alt',
            'fas fa-map-pin',
            'fas fa-map-signs',
            'fas fa-mars',
            'fas fa-mars-double',
            'fas fa-mars-stroke',
            'fas fa-mars-stroke-h',
            'fas fa-mars-stroke-v',
            'fas fa-medkit',
            'fas fa-meh',
            'fas fa-mercury',
            'fas fa-microchip',
            'fas fa-microphone',
            'fas fa-microphone-slash',
            'fas fa-minus',
            'fas fa-minus-circle',
            'fas fa-minus-square',
            'fas fa-mobile',
            'fas fa-mobile-alt',
            'fas fa-money-bill-alt',
            'fas fa-moon',
            'fas fa-motorcycle',
            'fas fa-mouse-pointer',
            'fas fa-music',
            'fas fa-neuter',
            'fas fa-newspaper',
            'fas fa-object-group',
            'fas fa-object-ungroup',
            'fas fa-outdent',
            'fas fa-paint-brush',
            'fas fa-paper-plane',
            'fas fa-paperclip',
            'fas fa-paragraph',
            'fas fa-paste',
            'fas fa-pause',
            'fas fa-pause-circle',
            'fas fa-paw',
            'fas fa-pen-square',
            'fas fa-pencil-alt',
            'fas fa-percent',
            'fas fa-phone',
            'fas fa-phone-square',
            'fas fa-phone-volume',
            'fas fa-plane',
            'fas fa-play',
            'fas fa-play-circle',
            'fas fa-plug',
            'fas fa-plus',
            'fas fa-plus-circle',
            'fas fa-plus-square',
            'fas fa-podcast',
            'fas fa-pound-sign',
            'fas fa-power-off',
            'fas fa-print',
            'fas fa-puzzle-piece',
            'fas fa-qrcode',
            'fas fa-question',
            'fas fa-question-circle',
            'fas fa-quidditch',
            'fas fa-quote-left',
            'fas fa-quote-right',
            'fas fa-random',
            'fas fa-recycle',
            'fas fa-redo',
            'fas fa-redo-alt',
            'fas fa-registered',
            'fas fa-reply',
            'fas fa-reply-all',
            'fas fa-retweet',
            'fas fa-road',
            'fas fa-rocket',
            'fas fa-rss',
            'fas fa-rss-square',
            'fas fa-ruble-sign',
            'fas fa-rupee-sign',
            'fas fa-save',
            'fas fa-search',
            'fas fa-search-minus',
            'fas fa-search-plus',
            'fas fa-server',
            'fas fa-share',
            'fas fa-share-alt',
            'fas fa-share-alt-square',
            'fas fa-share-square',
            'fas fa-shekel-sign',
            'fas fa-shield-alt',
            'fas fa-ship',
            'fas fa-shopping-bag',
            'fas fa-shopping-basket',
            'fas fa-shopping-cart',
            'fas fa-shower',
            'fas fa-sign-in-alt',
            'fas fa-sign-language',
            'fas fa-sign-out-alt',
            'fas fa-signal',
            'fas fa-sitemap',
            'fas fa-sliders-h',
            'fas fa-smile',
            'fas fa-snowflake',
            'fas fa-sort',
            'fas fa-sort-alpha-down',
            'fas fa-sort-alpha-up',
            'fas fa-sort-amount-down',
            'fas fa-sort-amount-up',
            'fas fa-sort-down',
            'fas fa-sort-numeric-down',
            'fas fa-sort-numeric-up',
            'fas fa-sort-up',
            'fas fa-space-shuttle',
            'fas fa-spinner',
            'fas fa-square-full',
            'fas fa-star',
            'fas fa-star-half',
            'fas fa-step-backward',
            'fas fa-step-forward',
            'fas fa-stethoscope',
            'fas fa-sticky-note',
            'fas fa-stop',
            'fas fa-stop-circle',
            'fas fa-stopwatch',
            'fas fa-street-view',
            'fas fa-strikethrough',
            'fas fa-subscript',
            'fas fa-subway',
            'fas fa-suitcase',
            'fas fa-sun',
            'fas fa-superscript',
            'fas fa-sync',
            'fas fa-sync-alt',
            'fas fa-table',
            'fas fa-tablet',
            'fas fa-tablet-alt',
            'fas fa-tachometer-alt',
            'fas fa-tag',
            'fas fa-tags',
            'fas fa-tasks',
            'fas fa-taxi',
            'fas fa-terminal',
            'fas fa-text-height',
            'fas fa-text-width',
            'fas fa-th',
            'fas fa-th-large',
            'fas fa-th-list',
            'fas fa-thermometer-empty',
            'fas fa-thermometer-full',
            'fas fa-thermometer-half',
            'fas fa-thermometer-quarter',
            'fas fa-thermometer-three-quarters',
            'fas fa-thumbs-down',
            'fas fa-thumbs-up',
            'fas fa-thumbtack',
            'fas fa-ticket-alt',
            'fas fa-times',
            'fas fa-times-circle',
            'fas fa-tint',
            'fas fa-toggle-off',
            'fas fa-toggle-on',
            'fas fa-trademark',
            'fas fa-train',
            'fas fa-transgender',
            'fas fa-transgender-alt',
            'fas fa-trash',
            'fas fa-trash-alt',
            'fas fa-tree',
            'fas fa-trophy',
            'fas fa-truck',
            'fas fa-tty',
            'fas fa-tv',
            'fas fa-umbrella',
            'fas fa-underline',
            'fas fa-undo',
            'fas fa-undo-alt',
            'fas fa-universal-access',
            'fas fa-university',
            'fas fa-unlink',
            'fas fa-unlock',
            'fas fa-unlock-alt',
            'fas fa-upload',
            'fas fa-user',
            'fas fa-user-circle',
            'fas fa-user-md',
            'fas fa-user-plus',
            'fas fa-user-secret',
            'fas fa-user-times',
            'fas fa-users',
            'fas fa-utensil-spoon',
            'fas fa-utensils',
            'fas fa-venus',
            'fas fa-venus-double',
            'fas fa-venus-mars',
            'fas fa-video',
            'fas fa-volume-down',
            'fas fa-volume-off',
            'fas fa-volume-up',
            'fas fa-wheelchair',
            'fas fa-wifi',
            'fas fa-window-close',
            'fas fa-window-maximize',
            'fas fa-window-minimize',
            'fas fa-window-restore',
            'fas fa-won-sign',
            'fas fa-wrench',
            'fas fa-yen-sign',
            'fab fa-500px',
            'fab fa-accessible-icon',
            'fab fa-accusoft',
            'fab fa-adn',
            'fab fa-adversal',
            'fab fa-affiliatetheme',
            'fab fa-algolia',
            'fab fa-amazon',
            'fab fa-amazon-pay',
            'fab fa-amilia',
            'fab fa-android',
            'fab fa-angellist',
            'fab fa-angrycreative',
            'fab fa-angular',
            'fab fa-app-store',
            'fab fa-app-store-ios',
            'fab fa-apper',
            'fab fa-apple',
            'fab fa-apple-pay',
            'fab fa-asymmetrik',
            'fab fa-audible',
            'fab fa-autoprefixer',
            'fab fa-avianex',
            'fab fa-aviato',
            'fab fa-aws',
            'fab fa-bandcamp',
            'fab fa-behance',
            'fab fa-behance-square',
            'fab fa-bimobject',
            'fab fa-bitbucket',
            'fab fa-bitcoin',
            'fab fa-bity',
            'fab fa-black-tie',
            'fab fa-blackberry',
            'fab fa-blogger',
            'fab fa-blogger-b',
            'fab fa-bluetooth',
            'fab fa-bluetooth-b',
            'fab fa-btc',
            'fab fa-buromobelexperte',
            'fab fa-buysellads',
            'fab fa-cc-amazon-pay',
            'fab fa-cc-amex',
            'fab fa-cc-apple-pay',
            'fab fa-cc-diners-club',
            'fab fa-cc-discover',
            'fab fa-cc-jcb',
            'fab fa-cc-mastercard',
            'fab fa-cc-paypal',
            'fab fa-cc-stripe',
            'fab fa-cc-visa',
            'fab fa-centercode',
            'fab fa-chrome',
            'fab fa-cloudscale',
            'fab fa-cloudsmith',
            'fab fa-cloudversify',
            'fab fa-codepen',
            'fab fa-codiepie',
            'fab fa-connectdevelop',
            'fab fa-contao',
            'fab fa-cpanel',
            'fab fa-creative-commons',
            'fab fa-css3',
            'fab fa-css3-alt',
            'fab fa-cuttlefish',
            'fab fa-d-and-d',
            'fab fa-dashcube',
            'fab fa-delicious',
            'fab fa-deploydog',
            'fab fa-deskpro',
            'fab fa-deviantart',
            'fab fa-digg',
            'fab fa-digital-ocean',
            'fab fa-discord',
            'fab fa-discourse',
            'fab fa-dochub',
            'fab fa-docker',
            'fab fa-draft2digital',
            'fab fa-dribbble',
            'fab fa-dribbble-square',
            'fab fa-dropbox',
            'fab fa-drupal',
            'fab fa-dyalog',
            'fab fa-earlybirds',
            'fab fa-edge',
            'fab fa-ember',
            'fab fa-empire',
            'fab fa-envira',
            'fab fa-erlang',
            'fab fa-ethereum',
            'fab fa-etsy',
            'fab fa-expeditedssl',
            'fab fa-facebook',
            'fab fa-facebook-f',
            'fab fa-facebook-messenger',
            'fab fa-facebook-square',
            'fab fa-firefox',
            'fab fa-first-order',
            'fab fa-firstdraft',
            'fab fa-flickr',
            'fab fa-fly',
            'fab fa-font-awesome',
            'fab fa-font-awesome-alt',
            'fab fa-font-awesome-flag',
            'fab fa-fonticons',
            'fab fa-fonticons-fi',
            'fab fa-fort-awesome',
            'fab fa-fort-awesome-alt',
            'fab fa-forumbee',
            'fab fa-foursquare',
            'fab fa-free-code-camp',
            'fab fa-freebsd',
            'fab fa-get-pocket',
            'fab fa-gg',
            'fab fa-gg-circle',
            'fab fa-git',
            'fab fa-git-square',
            'fab fa-github',
            'fab fa-github-alt',
            'fab fa-github-square',
            'fab fa-gitkraken',
            'fab fa-gitlab',
            'fab fa-gitter',
            'fab fa-glide',
            'fab fa-glide-g',
            'fab fa-gofore',
            'fab fa-goodreads',
            'fab fa-goodreads-g',
            'fab fa-google',
            'fab fa-google-drive',
            'fab fa-google-play',
            'fab fa-google-plus',
            'fab fa-google-plus-g',
            'fab fa-google-plus-square',
            'fab fa-google-wallet',
            'fab fa-gratipay',
            'fab fa-grav',
            'fab fa-gripfire',
            'fab fa-grunt',
            'fab fa-gulp',
            'fab fa-hacker-news',
            'fab fa-hacker-news-square',
            'fab fa-hips',
            'fab fa-hire-a-helper',
            'fab fa-hooli',
            'fab fa-hotjar',
            'fab fa-houzz',
            'fab fa-html5',
            'fab fa-hubspot',
            'fab fa-imdb',
            'fab fa-instagram',
            'fab fa-internet-explorer',
            'fab fa-ioxhost',
            'fab fa-itunes',
            'fab fa-itunes-note',
            'fab fa-jenkins',
            'fab fa-joget',
            'fab fa-joomla',
            'fab fa-js',
            'fab fa-js-square',
            'fab fa-jsfiddle',
            'fab fa-keycdn',
            'fab fa-kickstarter',
            'fab fa-kickstarter-k',
            'fab fa-korvue',
            'fab fa-laravel',
            'fab fa-lastfm',
            'fab fa-lastfm-square',
            'fab fa-leanpub',
            'fab fa-less',
            'fab fa-line',
            'fab fa-linkedin',
            'fab fa-linkedin-in',
            'fab fa-linode',
            'fab fa-linux',
            'fab fa-lyft',
            'fab fa-magento',
            'fab fa-maxcdn',
            'fab fa-medapps',
            'fab fa-medium',
            'fab fa-medium-m',
            'fab fa-medrt',
            'fab fa-meetup',
            'fab fa-microsoft',
            'fab fa-mix',
            'fab fa-mixcloud',
            'fab fa-mizuni',
            'fab fa-modx',
            'fab fa-monero',
            'fab fa-napster',
            'fab fa-nintendo-switch',
            'fab fa-node',
            'fab fa-node-js',
            'fab fa-npm',
            'fab fa-ns8',
            'fab fa-nutritionix',
            'fab fa-odnoklassniki',
            'fab fa-odnoklassniki-square',
            'fab fa-opencart',
            'fab fa-openid',
            'fab fa-opera',
            'fab fa-optin-monster',
            'fab fa-osi',
            'fab fa-page4',
            'fab fa-pagelines',
            'fab fa-palfed',
            'fab fa-patreon',
            'fab fa-paypal',
            'fab fa-periscope',
            'fab fa-phabricator',
            'fab fa-phoenix-framework',
            'fab fa-pied-piper',
            'fab fa-pied-piper-alt',
            'fab fa-pied-piper-pp',
            'fab fa-pinterest',
            'fab fa-pinterest-p',
            'fab fa-pinterest-square',
            'fab fa-playstation',
            'fab fa-product-hunt',
            'fab fa-pushed',
            'fab fa-python',
            'fab fa-qq',
            'fab fa-quinscape',
            'fab fa-quora',
            'fab fa-ravelry',
            'fab fa-react',
            'fab fa-rebel',
            'fab fa-red-river',
            'fab fa-reddit',
            'fab fa-reddit-alien',
            'fab fa-reddit-square',
            'fab fa-rendact',
            'fab fa-renren',
            'fab fa-replyd',
            'fab fa-resolving',
            'fab fa-rocketchat',
            'fab fa-rockrms',
            'fab fa-safari',
            'fab fa-sass',
            'fab fa-schlix',
            'fab fa-scribd',
            'fab fa-searchengin',
            'fab fa-sellcast',
            'fab fa-sellsy',
            'fab fa-servicestack',
            'fab fa-shirtsinbulk',
            'fab fa-simplybuilt',
            'fab fa-sistrix',
            'fab fa-skyatlas',
            'fab fa-skype',
            'fab fa-slack',
            'fab fa-slack-hash',
            'fab fa-slideshare',
            'fab fa-snapchat',
            'fab fa-snapchat-ghost',
            'fab fa-snapchat-square',
            'fab fa-soundcloud',
            'fab fa-speakap',
            'fab fa-spotify',
            'fab fa-square',
            'fab fa-stack-exchange',
            'fab fa-stack-overflow',
            'fab fa-staylinked',
            'fab fa-steam',
            'fab fa-steam-square',
            'fab fa-steam-symbol',
            'fab fa-sticker-mule',
            'fab fa-strava',
            'fab fa-stripe',
            'fab fa-stripe-s',
            'fab fa-studiovinari',
            'fab fa-stumbleupon',
            'fab fa-stumbleupon-circle',
            'fab fa-superpowers',
            'fab fa-supple',
            'fab fa-telegram',
            'fab fa-telegram-plane',
            'fab fa-tencent-weibo',
            'fab fa-themeisle',
            'fab fa-trello',
            'fab fa-tripadvisor',
            'fab fa-tumblr',
            'fab fa-tumblr-square',
            'fab fa-twitch',
            'fab fa-twitter',
            'fab fa-twitter-square',
            'fab fa-typo3',
            'fab fa-uber',
            'fab fa-uikit',
            'fab fa-uniregistry',
            'fab fa-untappd',
            'fab fa-usb',
            'fab fa-ussunnah',
            'fab fa-vaadin',
            'fab fa-viacoin',
            'fab fa-viadeo',
            'fab fa-viadeo-square',
            'fab fa-viber',
            'fab fa-vimeo',
            'fab fa-vimeo-square',
            'fab fa-vimeo-v',
            'fab fa-vine',
            'fab fa-vk',
            'fab fa-vnv',
            'fab fa-vuejs',
            'fab fa-weibo',
            'fab fa-weixin',
            'fab fa-whatsapp',
            'fab fa-whatsapp-square',
            'fab fa-whmcs',
            'fab fa-wikipedia-w',
            'fab fa-windows',
            'fab fa-wordpress',
            'fab fa-wordpress-simple',
            'fab fa-wpbeginner',
            'fab fa-wpexplorer',
            'fab fa-wpforms',
            'fab fa-xbox',
            'fab fa-xing',
            'fab fa-xing-square',
            'fab fa-y-combinator',
            'fab fa-yahoo',
            'fab fa-yandex',
            'fab fa-yandex-international',
            'fab fa-yelp',
            'fab fa-yoast',
            'fab fa-youtube'
        ];
    })();

    /**
     * Other UI
     */
    (function() {
        $(window).on('menu-builder-init', function(e, target) {
            var itemTemplate =
                '<li class="menu-builder-item" data-level="{LEVEL}">'
                    + '<div class="menu-builder-input input-group">'
                        + '<div class="input-group-prepend">'
                            + '<button class="menu-builder-handle btn btn-light" type="button">'
                                + '<i class="fas fa-arrows-alt fa-fw"></i>'
                            + '</button>'
                            + '<button class="menu-builder-handle btn btn-default" type="button">'
                                + '<i '
                                    + 'class="fas fa-question fa-fw" '
                                    + 'data-do="icon-field" '
                                    + 'data-target-parent="3"'
                                + '></i>'
                                + '<input '
                                    + 'class="form-control" '
                                    + 'data-name="icon" '
                                    + 'type="hidden" '
                                + '/>'
                            + '</button>'
                        + '</div>'
                        + '<input '
                            + 'class="form-control" '
                            + 'data-name="label" '
                            + 'placeholder="Menu Title" '
                            + 'type="text" '
                        + '/>'
                        + '<input '
                            + 'class="form-control" '
                            + 'data-name="path" '
                            + 'placeholder="/some/path" '
                            + 'type="text" '
                        + '/>'
                        + '<div class="input-group-append">'
                            + '{ACTION_ADD}'
                            + '<button class="btn btn-danger menu-builder-action-remove" type="button">'
                                + '<i class="fas fa-times"></i>'
                            + '</button>'
                        + '</div>'
                    + '</div>'
                    + '<ol class="menu-builder-list"></ol>'
                + '</li>';

            var addTemplate =
                '<button class="btn btn-success menu-builder-action-add" type="button">'
                    + '<i class="fas fa-plus"></i>'
                + '</button>';

            var depth = $(target).attr('data-depth') || 9;
            var message = $(target).attr('data-error') || 'Some items were empty';

            var reindex = function(list, level, path) {
                path = path || 'item';
                path += '[{INDEX}]';
                $(list).children('li.menu-builder-item').each(function(i) {
                    var newPath = path.replace('{INDEX}', i);
                    $('div.menu-builder-input:first', this).find('input').each(function() {
                        var name = $(this).attr('data-name');
                        if(!name.length) {
                            return;
                        }

                        $(this).attr('name', newPath + '[' + name + ']');
                    });

                    reindex($('ol.menu-builder-list:first', this), level + 1, newPath + '[children]');
                });
            };

            var listen = function(item, level) {
                //by default level 1
                level = level || 1;
                item = $(item);

                //on button add click
                $('button.menu-builder-action-add:first', item).click(function() {
                    //do we need the add action?
                    var add = '';
                    if(level < depth) {
                        add = addTemplate;
                    }

                    //make the template
                    var newItem = $(
                        itemTemplate
                            .replace('{LEVEL}', level)
                            .replace('{ACTION_ADD}', add)
                    ).doon();

                    //append the template
                    $('ol.menu-builder-list:first', item).append(newItem);

                    //reindex the names
                    reindex($('ol.menu-builder-list:first', target), level);

                    //listen to the item
                    listen(newItem, level + 1);
                });

                //on button remove click
                $('button.menu-builder-action-remove:first', item).click(function() {
                    $(this).closest('li.menu-builder-item').remove();

                    //reindex the names
                    reindex($('ol.menu-builder-list:first', target), level);
                });

                return item;
            };

            var checkForm = function(e) {
                var errors = false;
                $('input[data-name="label"]', target).each(function() {
                    if(!$(this).val().trim().length) {
                        $(this).parent().addClass('has-error');
                        errors = true;
                    }
                });

                $('input[data-name="path"]', target).each(function() {
                    if(!$(this).val().trim().length) {
                        $(this).parent().addClass('has-error');
                        errors = true;
                    }
                });

                if(errors) {
                    $('span.help-text', target).html(message);
                    e.preventDefault();
                    return false;
                }
            };

            //listen to the root
            listen(target)
                .submit(checkForm)
                //find all the current elements
                .find('li.menu-builder-item')
                .each(function() {
                    listen(this).doon();
                });

            var root = $('ol.menu-builder-list:first');

            root.sortable({
                onDrop: function ($item, container, _super, event) {
                    $item.removeClass(container.group.options.draggedClass).removeAttr('style');
                    $('body').removeClass(container.group.options.bodyClass);

                    setTimeout(function() {
                        reindex(root, 1);
                    }, 10);
                }
            });

            reindex(root, 1);
        });
    })();

    /**
     * Notifier
     */
    (function() {
        $(window).on('notify-init', function(e, trigger) {
            var timeout = parseInt($(trigger).attr('data-timeout') || 3000);

            if(!timeout) {
                return;
            }

            setTimeout(function() {
                $(trigger).fadeOut('fast', function() {
                    $(trigger).remove();
                });

            }, timeout);
        });

        $.extend({
            notify: function(message, type, timeout) {
                if(type === 'danger') {
                    type = 'error';
                }

                toastr.success('We do have the Kapua suite available.', 'Turtle Bay Resort', {timeOut: 20000000})

                toastr[type](message, type[0].toUpperCase() + type.substr(1), {
                    timeOut: timeout
                });
            }
        })
    })();

    //activate all scripts
    $(document.body).doon();
});
