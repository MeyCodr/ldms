<?php
session_start();

if (isset($_SESSION['fullname']) && ($_SESSION['role'] == 'ADMIN')) {
    include "../../tna_training_options.php";
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Learning and Development Management System</title>
        <script src="../../asset/js/jquery-1.10.2.min.js"></script>
        <link rel="stylesheet" href="../../asset/css/bootstrap.min.css" />
        <script src="../../asset/js/bootstrap.min.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
    </head>

    <style>
        #optlist td { vertical-align: middle; }
        #optlist tr.group-row td { background: #d9edf7; font-weight: bold; }
        #optlist tr.is-hidden td.opt-name { color: #999; text-decoration: line-through; }
        #optlist .btn-xs { margin: 1px; }
        #optlist tr.others-row td { color: #777; font-style: italic; }
        .nav-tabs > li > a { font-weight: bold; }
    </style>

    <body onload="startTime()" style="background-image:url('../../asset/image/bg-try.png');zoom: 75%;">
        <br>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10">
                    <img src="../../asset/image/lndlogo.gif" height="50" width="290">
                </div>
                <div id="txt" align="right" class="col-md-2" style="margin-top:43px;color:white;">

                </div>
            </div>
            <nav class="navbar navbar-inverse">
                <div class="container-fluid ">
                    <ul class="nav navbar-nav">
                        <li><a href="../dashboard.php">HOME</a></li>
                        <li><a href="../staff/staff.php">STAFF LIST</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span
                                    class="label label-pill label-danger count"></span> ALL TRAINING </a>
                            <ul class="dropdown-menu">
                                <li><a href="../training/public/training.php">PUBLIC/INHOUSE</a></li>
                                <li><a href="../training/ojt/training_ojt.php">OJT</a></li>
                                <li><a href="../training/departmental/training_dept.php">DEPARTMENTAL</a></li>
                            </ul>
                        </li>
                        <li><a href="../attendance/training.php">MY TRAINING</a></li>
                        <li class="active"><a href="tna_list.php">TNA</a></li>
                        <li><a href="../tni/tni_list.php">TNI</a></li>
                        <li><a href="../tna/tna_summary.php">TNA SUMMARY</a></li>
                        <li><a href="../skill-matrix/skill-matrix.php">SKILL MATRIX</a></li>
                        <li><a href="../organization/org.php">ORGANIZATION</a></li>
                        <li><a href="../archive/export.php">EXPORT</a></li>
                        <li><a href="../password/password.php">CHANGE PASSWORD</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span
                                    class="label label-pill label-danger count"></span> <?php echo $_SESSION['fullname'] ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="../../logout.php">LOGOUT</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-2" align="left">
                                    <a href="tna_list.php" class="btn btn-success btn-md"><i class="far fa-arrow-alt-circle-left"></i> BACK TO TNA LIST</a>
                                </div>
                                <div class="col-md-8" style="margin-top:10px" align="center">
                                    <strong>TNA Training Options (Training Required dropdown)</strong>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <ul class="nav nav-tabs" id="sectiontabs">
                                <?php foreach (tna_training_sections() as $key => $label) { ?>
                                    <li><a href="#" data-section="<?php echo $key ?>"><?php echo tna_training_h($label) ?></a></li>
                                <?php } ?>
                            </ul>
                            <br>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="alert alert-info" style="margin-bottom:10px;">
                                        <i class="fas fa-info-circle"></i>
                                        Changes show up on every TNA form (staff, HOD, clerk and admin) straight away.
                                        <b>OTHERS</b> is always added automatically at the bottom of each list.
                                        <b>Hide</b> an option to stop it being picked for new TNAs. Staff who already chose it keep it.
                                        Only options that no saved TNA uses can be <b>deleted</b>.
                                    </div>
                                </div>
                                <div class="col-md-4" align="right">
                                    <button type="button" id="btn_add_option" class="btn btn-primary"><i class="fas fa-plus"></i> ADD OPTION</button>
                                    <button type="button" id="btn_add_group" class="btn btn-default"><i class="fas fa-folder-plus"></i> ADD GROUP</button>
                                    <div style="margin-top:6px;">
                                        <a href="training_options_export.php" class="btn btn-success"><i class="fas fa-file-excel"></i> DOWNLOAD EXCEL</a>
                                        <button type="button" id="btn_import" class="btn btn-warning"><i class="fas fa-file-upload"></i> IMPORT EXCEL</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8 table-responsive">
                                    <table id="optlist" class="table table-bordered table-condensed">
                                        <thead>
                                            <tr>
                                                <th width="50px">No.</th>
                                                <th>Training Required</th>
                                                <th width="110px" title="Saved TNA rows using this option">Used in TNA</th>
                                                <th width="90px">Status</th>
                                                <th width="290px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div class="col-md-4">
                                    <div class="panel panel-default">
                                        <div class="panel-heading"><strong>Preview: what staff see</strong></div>
                                        <div class="panel-body">
                                            <select id="preview" class="form-control"></select>
                                            <small class="text-muted">Hidden options do not appear here.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add / edit option -->
        <div class="modal fade" id="modalOption" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title" id="modalOptionTitle">Option</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="opt_id">
                        <div class="form-group">
                            <label>Training name</label>
                            <input type="text" id="opt_name" class="form-control" maxlength="255" autocomplete="off" style="text-transform:uppercase;">
                        </div>
                        <div class="form-group" id="opt_group_wrap">
                            <label>Group</label>
                            <select id="opt_group" class="form-control"></select>
                        </div>
                        <div class="alert alert-warning" id="opt_rename_note" style="display:none;margin-bottom:0;"></div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" id="opt_save">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add / rename group -->
        <div class="modal fade" id="modalGroup" tabindex="-1">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title" id="modalGroupTitle">Group</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="grp_id">
                        <label>Group name</label>
                        <input type="text" id="grp_name" class="form-control" maxlength="255" autocomplete="off" style="text-transform:uppercase;">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" id="grp_save">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Excel import -->
        <div class="modal fade" id="modalImport" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Import Training Options from Excel</h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            Use the file from <b>DOWNLOAD EXCEL</b>, edit it, then upload it here. The <b>How to use</b> sheet in the file explains each change.
                            You will see every change before anything is saved.
                        </p>
                        <input type="file" id="import_file" accept=".xlsx,.xls" class="form-control">
                        <div id="import_result" style="margin-top:15px;"></div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-info" id="import_preview"><i class="fas fa-search"></i> Preview</button>
                        <button class="btn btn-warning" id="import_apply" disabled><i class="fas fa-check"></i> Confirm Import</button>
                    </div>
                </div>
            </div>
        </div>
    </body>

    <script>
        function startTime() {
            var today = new Date();
            var h = today.getHours();
            var m = today.getMinutes();
            var s = today.getSeconds();
            // add a zero in front of numbers<10
            h = checkTime(h);
            m = checkTime(m);
            s = checkTime(s);
            document.getElementById('txt').innerHTML = h + ":" + m + ":" + s;
            t = setTimeout(function () {
                startTime()
            }, 500);
        }

        function checkTime(i) {
            if (i < 10) {
                i = "0" + i;
            }
            return i;
        }

        var section = 'esgaware';
        var cats = [];
        var opts = [];

        function esc(s) {
            return $('<div>').text(s == null ? '' : s).html();
        }

        function post(data, done) {
            $.ajax({
                url: 'training_options_action.php',
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function (r) {
                    if (r.message == 'error') {
                        swal('Error', r.detail, 'error');
                    } else {
                        done(r);
                    }
                },
                error: function () {
                    swal('Error', 'Could not reach the server. Please try again.', 'error');
                }
            });
        }

        function optionRow(o, no) {
            var used = parseInt(o.used, 10);
            var active = o.is_active == 1;
            return '<tr class="' + (active ? '' : 'is-hidden') + '" title="Last changed by ' + esc(o.updated_by || '-') + ' on ' + esc(o.updated_at) + '">' +
                '<td align="center">' + no + '</td>' +
                '<td class="opt-name">' + esc(o.name) + '</td>' +
                '<td align="center">' + (used ? '<span class="badge">' + used + '</span>' : '-') + '</td>' +
                '<td align="center">' + (active ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Hidden</span>') + '</td>' +
                '<td>' +
                '<button class="btn btn-default btn-xs opt-move" data-id="' + o.id + '" data-dir="up" title="Move up"><i class="fas fa-arrow-up"></i></button>' +
                '<button class="btn btn-default btn-xs opt-move" data-id="' + o.id + '" data-dir="down" title="Move down"><i class="fas fa-arrow-down"></i></button>' +
                '<button class="btn btn-info btn-xs opt-edit" data-id="' + o.id + '"><i class="fas fa-pen"></i> Edit</button>' +
                '<button class="btn btn-warning btn-xs opt-toggle" data-id="' + o.id + '"><i class="fas fa-eye' + (active ? '-slash' : '') + '"></i> ' + (active ? 'Hide' : 'Show') + '</button>' +
                '<button class="btn btn-danger btn-xs opt-delete" data-id="' + o.id + '"' + (used ? ' disabled title="Used by saved TNAs - hide it instead"' : '') + '><i class="fas fa-trash"></i> Delete</button>' +
                '</td></tr>';
        }

        function othersRow() {
            return '<tr class="others-row"><td></td><td>OTHERS (automatic)</td><td></td><td></td><td></td></tr>';
        }

        function render() {
            var html = '';
            var no = 0;
            var ungrouped = $.grep(opts, function (o) { return !o.category_id; });
            $.each(ungrouped, function (i, o) { html += optionRow(o, ++no); });
            $.each(cats, function (i, c) {
                html += '<tr class="group-row"><td colspan="4"><i class="fas fa-folder-open"></i> ' + esc(c.name) + '</td><td>' +
                    '<button class="btn btn-default btn-xs grp-move" data-id="' + c.id + '" data-dir="up" title="Move group up"><i class="fas fa-arrow-up"></i></button>' +
                    '<button class="btn btn-default btn-xs grp-move" data-id="' + c.id + '" data-dir="down" title="Move group down"><i class="fas fa-arrow-down"></i></button>' +
                    '<button class="btn btn-info btn-xs grp-edit" data-id="' + c.id + '"><i class="fas fa-pen"></i> Rename</button>' +
                    '<button class="btn btn-danger btn-xs grp-delete" data-id="' + c.id + '"><i class="fas fa-trash"></i> Delete</button>' +
                    '</td></tr>';
                $.each($.grep(opts, function (o) { return o.category_id == c.id; }), function (j, o) { html += optionRow(o, ++no); });
                html += othersRow();
            });
            if (!cats.length) html += othersRow();
            $('#optlist tbody').html(html);
        }

        function load() {
            post({ btn_action: 'list', section: section }, function (r) {
                cats = r.categories;
                opts = r.options;
                render();
                $('#preview').html('<option selected disabled>-- Select Training --</option>' + r.preview);
            });
        }

        function findOpt(id) {
            return $.grep(opts, function (o) { return o.id == id; })[0];
        }

        function findCat(id) {
            return $.grep(cats, function (c) { return c.id == id; })[0];
        }

        function openOption(o) {
            $('#opt_id').val(o ? o.id : '');
            $('#opt_name').val(o ? o.name : '');
            $('#modalOptionTitle').text(o ? 'Edit Option' : 'Add Option - ' + $('#sectiontabs li.active a').text());
            var sel = '<option value="0">(No group)</option>';
            $.each(cats, function (i, c) { sel += '<option value="' + c.id + '">' + esc(c.name) + '</option>'; });
            $('#opt_group').html(sel).val(o && o.category_id ? o.category_id : (cats.length ? cats[cats.length - 1].id : 0));
            $('#opt_group_wrap').toggle(cats.length > 0);
            var used = o ? parseInt(o.used, 10) : 0;
            $('#opt_rename_note').toggle(used > 0).html(used > 0 ?
                '<i class="fas fa-exclamation-triangle"></i> ' + used + ' saved TNA row(s) use this option. If you change the name, those rows will be updated to the new name too.' : '');
            $('#modalOption').modal('show');
            setTimeout(function () { $('#opt_name').focus(); }, 400);
        }

        $(document).ready(function () {
            $('#sectiontabs a').click(function (e) {
                e.preventDefault();
                $('#sectiontabs li').removeClass('active');
                $(this).parent().addClass('active');
                section = $(this).data('section');
                load();
            });
            $('#sectiontabs a:first').click();

            $('#btn_add_option').click(function () { openOption(null); });

            $(document).on('click', '.opt-edit', function () { openOption(findOpt($(this).data('id'))); });

            $('#opt_save').click(function () {
                var id = $('#opt_id').val();
                post({
                    btn_action: id ? 'edit_option' : 'add_option',
                    id: id,
                    section: section,
                    name: $('#opt_name').val(),
                    category_id: $('#opt_group').val()
                }, function (r) {
                    $('#modalOption').modal('hide');
                    swal('Saved', r.renamed ? r.renamed + ' saved TNA row(s) were updated to the new name.' : 'The option list has been updated.', 'success');
                    load();
                });
            });

            $('#opt_name, #grp_name').keypress(function (e) {
                if (e.which == 13) $(this).closest('.modal-content').find('.btn-primary').click();
            });

            $(document).on('click', '.opt-toggle', function () {
                var o = findOpt($(this).data('id'));
                post({ btn_action: 'toggle_option', id: o.id }, load);
            });

            $(document).on('click', '.opt-delete', function () {
                var o = findOpt($(this).data('id'));
                swal({
                    title: 'Delete this option?',
                    text: o.name,
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true
                }).then(function (ok) {
                    if (ok) post({ btn_action: 'delete_option', id: o.id }, load);
                });
            });

            $(document).on('click', '.opt-move', function () {
                post({ btn_action: 'move_option', id: $(this).data('id'), dir: $(this).data('dir') }, load);
            });

            $('#btn_add_group').click(function () {
                $('#grp_id').val('');
                $('#grp_name').val('');
                $('#modalGroupTitle').text('Add Group');
                $('#modalGroup').modal('show');
                setTimeout(function () { $('#grp_name').focus(); }, 400);
            });

            $(document).on('click', '.grp-edit', function () {
                var c = findCat($(this).data('id'));
                $('#grp_id').val(c.id);
                $('#grp_name').val(c.name);
                $('#modalGroupTitle').text('Rename Group');
                $('#modalGroup').modal('show');
            });

            $('#grp_save').click(function () {
                var id = $('#grp_id').val();
                post({ btn_action: id ? 'rename_category' : 'add_category', id: id, section: section, name: $('#grp_name').val() }, function () {
                    $('#modalGroup').modal('hide');
                    load();
                });
            });

            $(document).on('click', '.grp-delete', function () {
                var c = findCat($(this).data('id'));
                swal({
                    title: 'Delete this group?',
                    text: c.name + '\n\nOnly empty groups can be deleted.',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true
                }).then(function (ok) {
                    if (ok) post({ btn_action: 'delete_category', id: c.id }, load);
                });
            });

            $(document).on('click', '.grp-move', function () {
                post({ btn_action: 'move_category', id: $(this).data('id'), dir: $(this).data('dir') }, load);
            });

            // ===== EXCEL IMPORT =====
            $('#btn_import').click(function () {
                $('#import_file').val('');
                $('#import_result').html('');
                $('#import_apply').prop('disabled', true);
                $('#modalImport').modal('show');
            });

            $('#import_file').change(function () {
                $('#import_result').html('');
                $('#import_apply').prop('disabled', true);
            });

            function sendImport(mode, done) {
                var file = $('#import_file')[0].files[0];
                if (!file) {
                    swal('No file', 'Please choose the Excel file first.', 'warning');
                    return;
                }
                var fd = new FormData();
                fd.append('import_file', file);
                fd.append('mode', mode);
                $('#import_preview, #import_apply').prop('disabled', true);
                $.ajax({
                    url: 'training_options_import.php',
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: done,
                    error: function () {
                        swal('Error', 'Could not reach the server. Please try again.', 'error');
                    },
                    complete: function () {
                        $('#import_preview').prop('disabled', false);
                    }
                });
            }

            function summaryText(s) {
                var parts = [];
                if (s.added) parts.push(s.added + ' added');
                if (s.renamed) parts.push(s.renamed + ' renamed');
                if (s.moved) parts.push(s.moved + ' moved to another group');
                if (s.hidden) parts.push(s.hidden + ' hidden');
                if (s.shown) parts.push(s.shown + ' shown again');
                if (s.groups_added) parts.push(s.groups_added + ' new group(s)');
                if (s.reordered.length) parts.push('order changed in ' + s.reordered.length + ' section(s)');
                return parts.join(', ');
            }

            $('#import_preview').click(function () {
                sendImport('preview', function (r) {
                    var html = '';
                    if (r.message == 'error') {
                        html = '<div class="alert alert-danger">' + esc(r.detail) + '</div>';
                    } else if (r.message == 'invalid') {
                        html = '<div class="alert alert-danger"><b>' + r.errors.length + ' problem(s) found. Nothing can be imported until they are fixed in the file:</b></div>' +
                            '<table class="table table-condensed table-bordered"><thead><tr><th width="70px">Row</th><th>Problem</th></tr></thead><tbody>';
                        $.each(r.errors, function (i, e) {
                            html += '<tr><td align="center">' + e.row + '</td><td>' + esc(e.detail) + '</td></tr>';
                        });
                        html += '</tbody></table>';
                    } else if (!r.has_changes) {
                        html = '<div class="alert alert-info">The file matches the current lists. There is nothing to import.</div>';
                    } else {
                        var s = r.summary;
                        html = '<div class="alert alert-warning"><b>' + esc(summaryText(s)) + '.</b>' +
                            (s.tna_rows ? '<br><i class="fas fa-exclamation-triangle"></i> ' + s.tna_rows + ' saved TNA row(s) will be updated to the new names.' : '') +
                            '<br>Click <b>Confirm Import</b> to apply these changes.</div>' +
                            '<div style="max-height:350px;overflow-y:auto;"><table class="table table-condensed table-bordered"><thead><tr><th width="70px">Row</th><th>Change</th></tr></thead><tbody>';
                        $.each(r.changes, function (i, c) {
                            html += '<tr><td align="center">' + (c.row || '') + '</td><td>' + esc(c.text) + '</td></tr>';
                        });
                        html += '</tbody></table></div>';
                        $('#import_apply').prop('disabled', false);
                    }
                    $('#import_result').html(html);
                });
            });

            $('#import_apply').click(function () {
                sendImport('apply', function (r) {
                    if (r.message == 'done') {
                        $('#modalImport').modal('hide');
                        swal('Imported', (summaryText(r.summary) || 'No changes') + '.', 'success');
                        load();
                    } else if (r.message == 'invalid') {
                        swal('Not imported', 'The file has problems. Click Preview to see them.', 'error');
                    } else {
                        swal('Not imported', r.detail || 'Something went wrong. Nothing was changed.', 'error');
                    }
                });
            });
        });
    </script>

    </html>
    <?php
} else {
    header("Location: ../../login.php");
    exit();
}
?>
