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

            var searching = false,
                prevent = false,
                label = target.attr('data-label'),
                value = target.attr('data-value'),
                url = target.attr('data-url')
                template = '<li class="suggestion-item">{VALUE}</li>';

            if(!label || !value || !url) {
                return;
            }

            label = $(label);
            value = $(value);

            var loadSuggestions = function(list, callback) {
                target.html('');

                list.forEach(function(item) {
                    var row = template.replace('{VALUE}', item.label);

                    row = $(row).click(function() {
                        callback(item);
                        target.addClass('d-none');
                    });

                    target.append(row);
                });

                if(list.length) {
                    target.removeClass('d-none');
                } else {
                    target.addClass('d-none');
                }
            };

            label
                .keypress(function(e) {
                    if(e.keyCode == 13 && prevent) {
                        e.preventDefault();
                    }
                })
                .keydown(function(e) {
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
                        if (label.val() == '') {
                            return;
                        }

                        searching = true;

                        $.ajax({
                            url : url.replace('{QUERY}', label.val()),
                            type : 'GET',
                            success : function(response) {
                                var list = [];

                                if(typeof response === 'string' || typeof response === 'number') {
                                    response = [response];
                                }

                                if(response instanceof Array) {
                                    response.forEach(function(item) {
                                        list.push(item);
                                    });
                                }

                                loadSuggestions(list, function(item) {
                                    value.val(item.value);
                                    label.val(item.label).trigger('keyup');
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
                row:
                    '<tr class="file-field-item">'
                    + '<td class="file-field-preview">{PREVIEW}</td>'
                    + '<td class="file-field-name">{NAME}</td>'
                    + '<td class="file-field-mime">{MIME}</td>'
                    + '<td class="file-field-size">{SIZE}</td>'
                    + '<td class="file-field-actions">'
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
                    + '</td>'
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

            var generate = function(file, name, width, height) {
                var reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function () {
                    var extension = file.name.split('.').pop();

                    if(file.name.indexOf('.') === -1) {
                        extension = '???';
                    }

                    var preview = template.previewFile.replace('{EXTENSION}', extension);

                    if(file.type.indexOf('image/') === 0) {
                        preview = template.previewImage.replace('{DATA}', reader.result);
                    }

                    noresults.hide();

                    var row = $(
                        template.row
                            .replace('{PREVIEW}', preview)
                            .replace('{NAME}', file.name)
                            .replace('{MIME}', file.type)
                            .replace('{SIZE}', file.size)
                    ).appendTo(body);

                    //create input tags
                    var hidden = $('<input type="hidden" />')
                        .attr('name', name)
                        .val(reader.result);

                    $('td.file-field-actions', row).append(hidden);

                    listen(row, body);

                    if(file.type.indexOf('image/') === 0 && (width !== 0 || height !== 0)) {
                        //so we can crop
                        $.cropper(file, width, height, function(data) {
                            $('div.file-field-preview-container img', row).attr('src', data);
                            hidden.val(data);
                        });
                    }
                };

            };

            file.change(function() {
                if(!this.files || !this.files[0]) {
                    return;
                }

                //remove all
                $('input[type="hidden"]', target).remove();

                for(var path = '', i = 0; i < this.files.length; i++, path = '') {
                    if(multiple) {
                        path = '[]' + path;
                    }

                    path = name + path;

                    generate(this.files[i], path, width, height);
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
                stylesheets:  '/styles/custom.css'
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

        /**
         * Select Csv to Import
         */
        $(window).on('select-csv-click', function(e, target) {
            e.preventDefault();
            var target = $(target);
            $("#import-csv").trigger('click');
        });

        /**
         * import csv
         */
        $(window).on('import-csv-change', function(e, target) {
            var target = $(target);
            target.closest('#importForm').submit();
        });

        /**
         * Select Csv to Export
         */
        $(window).on('export-csv-click', function(e, target) {
            e.preventDefault();
            var target = $(target);
            $("#exportForm").submit();
        });

        /**
         * Search submit search form
         */
        $(window).on('object-search-click', function(e, target) {
            var target = $(target);
            var form = target.parents('form');

            form.submit(function() {
                form.find(":input").filter(
                    function() {
                        return !this.value;
                    }).attr("disabled", "disabled");

                return true; // ensure form still submits
            });

            // Un-disable form fields when page loads, in case they click back after submission
            form.find( ":input" ).prop( "disabled", false );
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
