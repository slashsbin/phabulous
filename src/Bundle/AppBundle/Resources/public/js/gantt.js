// Basic
gantt.config.xml_date = '%Y-%m-%d';
gantt.config.readonly = true;
gantt.config.show_errors = true;
gantt.config.show_task_cells = true;
gantt.config.sort = true;
gantt.config.show_unscheduled = true;
// Drag-n-Drop
gantt.config.drag_lightbox = false;
gantt.config.drag_move = false;
gantt.config.drag_progress = false;
gantt.config.drag_resize = false;
gantt.config.drag_links = false;
gantt.config.order_branch = false;
gantt.config.order_branch_free = false;
gantt.config.multiselect = false;
// Scroll
gantt.config.initial_scroll = false;
gantt.config.scroll_on_click = true;
gantt.config.task_scroll_offset = 100;
gantt.config.preserve_scroll = true;
// Scale & Duration
gantt.config.scale_unit = 'month';
gantt.config.step = 1;
gantt.config.date_scale = '%F, %Y';
gantt.config.scale_height = 90;
gantt.config.subscales = [
    {unit: 'week', step: 1, date: 'Week #%W'},
    {
        unit: 'day', step: 1, date: '%D, %j', css: function (date) {
        if (!gantt.isWorkTime(date, 'day')) {
            return 'week_end';
        }
    }
    }
];
gantt.config.duration_unit = 'hour';
gantt.config.duration_step = 1;
// Size
gantt.config.min_column_width = 50;
gantt.config.grid_width = 450;
// Work-Time
gantt.config.work_time = true;
gantt.config.skip_off_time = true;
gantt.config.correct_work_time = true;
gantt.config.round_dnd_dates = true;

$(function () {
    $(window).on('resize', function (e) {
        var ganttHeight = $(window).innerHeight() - $('.btn-toolbar').outerHeight() - $('.navbar-static-top').outerHeight();
        $('#myGanttChart').height(ganttHeight);
    }).resize();

    // Holidays & WorkTime
    gantt.setWorkTime({hours: [8, 12, 13, 17]}); //global working hours. 8:00-12:00, 13:00-17:00
    gantt.setWorkTime({day: 6, hours: [8, 12, 13, 17]});
    gantt.setWorkTime({day: 0, hours: [8, 12, 13, 17]});
    gantt.setWorkTime({day: 1, hours: [8, 12, 13, 17]});
    gantt.setWorkTime({day: 2, hours: [8, 12, 13, 17]});
    gantt.setWorkTime({day: 3, hours: [8, 12, 13, 17]});
    gantt.setWorkTime({day: 4, hours: false});
    gantt.setWorkTime({day: 5, hours: false});

    // Columns
    gantt.config.columns = [
        {
            name: 'tid', label: 'ID', align: 'left', width: 40, template: function (task) {
                var taskTitle = '';
                var taskClass = [];

                // Task Type
                switch (task.type) {
                    case gantt.config.types.task:
                        taskTitle = 'T' + task.tid;
                        break;
                    case gantt.config.types.project:
                        taskTitle = 'P' + task.tid;
                        break;
                }

                // Status
                if (!task.open) {
                    taskClass.push('text-muted');
                    taskTitle = '<del>%taskTitle</del>'.replace(/%taskTitle/, taskTitle);
                }

                return '<a href="%uri" target="_blank" class="%taskClass">%title</a>'
                    .replace(/%uri/, task.uri)
                    .replace(/%title/, taskTitle)
                    .replace(/%taskClass/, taskClass.join(' '));
            }
        },
        {
            name: 'text', label: 'Task name', align: 'left', width: '*', tree: true, template: function (task) {
                if (!task.open) {
                    return '<span class="text-muted">' + task.text + '</span>';
                }
                return task.text;
            }
        },
        {name: 'holder', label: 'Assigned To', align: 'center', width: 150},
        {
            name: 'progress', label: '%', align: 'center', width: 45, template: function (task) {
                return task.progress * 100 + '%';
            }
        }
    ];

    // Templates
    gantt.templates.task_class = function (start, end, task) {
        var taskClass = [];

        // Type
        switch (task.type) {
            case gantt.config.types.task:
                taskClass.push('bg-info');
                break;
            case gantt.config.types.project:
                taskClass.push('bg-success');
                break;
        }

        return taskClass.join(' ');
    };
    gantt.templates.grid_file = function(task) {
        var taskFileClass = [
            'gantt_tree_icon'
        ];
        var taskFileIconClass = [
            'icon'
        ];

        // Status
        if (task.open) {
            taskFileIconClass.push('ion-flag');
            taskFileClass.push(task.priority_color);
        } else {
            taskFileIconClass.push('ion-checkmark-circled');
            taskFileClass.push('text-success');
        }

        return '<div class="%taskFileClass"><i class="%taskFileIconClass"></i></div>'
            .replace(/%taskFileClass/, taskFileClass.join(' '))
            .replace(/%taskFileIconClass/, taskFileIconClass.join(' '));
    };
    gantt.templates.grid_blank = function(task) {
        var taskBlankClass = [
            'gantt_tree_icon'
        ];
        var taskBlankIconClass = [
            'icon'
        ];

        // Overdue
        if (task.overdue) {
            taskBlankIconClass.push('ion-alert-circled');
            taskBlankClass.push('text-danger');
        }

        return '<div class="%s1"><i class="%s2"></i></div>'
            .replace(/%s1/, taskBlankClass.join(' '))
            .replace(/%s2/, taskBlankIconClass.join(' '));
    };
    gantt.templates.task_text = function (start, end, task) {
        switch (task.type) {
            case gantt.config.types.task:
                return '<a href="' + task.uri + '" target="_blank">T' + task.tid + '</a>: ' + task.text;
                break;
            case gantt.config.types.project:
                return task.text;
                break;
            default:
                return task.text;
        }
    };
    gantt.templates.task_row_class = function (start, end, task) {
        var taskRowClass = [];

        if (task.overdue) {
            taskRowClass.push('bg-danger');
            taskRowClass.push('overdue');
        }

        return taskRowClass.join(' ');
    };
    gantt.templates.task_cell_class = function (task, date) {
        var taskCellClass = [];

        if (!gantt.isWorkTime(date, 'day')) {
            taskCellClass.push('week_end');
        }

        return taskCellClass.join(' ');
    };
    gantt.templates.scale_cell_class = function (date) {
        var scaleCellClass = [];

        if (!gantt.isWorkTime(date, 'day')) {
            scaleCellClass.push('week_end');
        }

        return scaleCellClass.join(' ');
    };
    // Reset Aria & ToolTip Text due a Bug in Calculation of InComplete Dates!
    gantt.templates.tooltip_text = function() { return ''; };

    // Markers
    gantt.addMarker({
        start_date: new Date(),
        text: 'Now'
    });

    // Initialize & Load Data
    gantt.init('myGanttChart');
    gantt.parse(JSON.parse($('#ganttData').html()));
    gantt.showDate(new Date());

    // View Events
    $('#gantt_view__full_screen').click(function () {
        if (!gantt.getState().fullscreen) {
            gantt.expand();
        }
        else {
            gantt.collapse();
        }
    });
    $('#scale-toolbar .dropdown-menu a').on('click', function () {
        $(this).closest('.dropdown-menu').find('.dropdown-item').each(function() {
            $(this).removeClass('active');
        });
        $(this).addClass('active');

        switch ($(this).data('scale')) {
            case 'day':
                gantt.config.scale_unit = "day";
                gantt.config.date_scale = "%d %M";
                gantt.config.subscales = [];
                gantt.config.scale_height = 27;
                gantt.templates.date_scale = null;
                break;
            case 'week':
                var weekScaleTemplate = function(date){
                    var dateToStr = gantt.date.date_to_str("%d %M");
                    var endDate = gantt.date.add(gantt.date.add(date, 1, "week"), -1, "day");
                    return dateToStr(date) + " - " + dateToStr(endDate);
                };

                gantt.config.scale_unit = "week";
                gantt.templates.date_scale = weekScaleTemplate;
                gantt.config.subscales = [
                    {unit:"day", step:1, date:"%D" }
                ];
                gantt.config.scale_height = 50;
                break;
            case 'month':
                gantt.config.scale_unit = "month";
                gantt.config.date_scale = "%F, %Y";
                gantt.config.subscales = [];
                gantt.config.scale_height = 50;
                gantt.templates.date_scale = null;
                break;
            case 'year':
                gantt.config.scale_unit = "year";
                gantt.config.date_scale = "%Y";
                gantt.config.min_column_width = 50;

                gantt.config.scale_height = 90;
                gantt.templates.date_scale = null;

                gantt.config.subscales = [
                    {unit:"month", step:1, date:"%M" }
                ];
                break;
            case 'custom':
                gantt.config.scale_unit = 'month';
                gantt.config.step = 1;
                gantt.config.date_scale = '%F, %Y';
                gantt.config.scale_height = 90;
                gantt.config.subscales = [
                    {unit: 'week', step: 1, date: 'Week #%W'},
                    {
                        unit: 'day', step: 1, date: '%D, %j', css: function (date) {
                        if (!gantt.isWorkTime(date, 'day')) {
                            return 'week_end';
                        }
                    }
                    }
                ];
                gantt.config.duration_unit = 'hour';
                gantt.config.duration_step = 1;
                break;
        }
        gantt.render();
    });
    // Export Events
    $('#gantt_export__png').click(function () {
        gantt.exportToPNG();
    });
    $('#gantt_export__excel').click(function () {
        gantt.exportToExcel();
    });
    $('#gantt_export__ical').click(function () {
        gantt.exportToICal();
    });
    $('#gantt_export__pdf').click(function () {
        gantt.exportToPDF();
    });
    $('#gantt_export__msproject').click(function () {
        gantt.exportToMSProject();
    });

    gantt.attachEvent("onAfterTaskDrag", function (id, mode, e) {
        var task = gantt.getTask(id);
        task.start_date = gantt.date.date_part(task.start_date);
        $.post(Routing.generate('phacility_edit_maniphest', {_format: 'json'}), {
            id: id,
            mode: mode,
            task: task
        }).done(function (data) {
            var task = gantt.getTask(data.id);
            task.start_date = data.start_date;
            task.duration = data.duration;
            gantt.refreshTask(task.id, true);
            gantt.message({
                text: 'Maniphest Task T' + data.tid + ' Updated Successfully.',
                expire: 3000
            });
        }).fail(function () {
            gantt.message({
                type: 'error',
                text: 'There was an Error!',
                expire: 3000
            });
        });
    });
});
