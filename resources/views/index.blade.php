<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task1 - Admin</title>
    <link rel="stylesheet" href="assets/bootstrap5.css">
    <script src="assets/bootstrap5.js"></script>
    <script src="assets/jquery.js"></script>
    <style>
        body {
            min-height: 100vh;
            padding: 30px 0px;
            background-color: var(--bs-gray-200);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form {
            padding-block: 20px;
        }

        .table-span {
            background-color: #f5f5f5;
            padding: 10px;
            height: 450px;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <h3 class="text-center">Welcome, Admin</h3>
        <h6 class="text-center small">enter task and expiry date</h6>
        <div class="form">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row justify-content-center g-3">
                        <div class="col-md-5">
                            <input type="text" class="form-control" id="tasktitle" placeholder="task title..">
                        </div>
                        <div class="col-md-4">
                            <input type="text" onfocus="(this.type='date')" class="form-control" placeholder="expiry date" id="taskdate">
                        </div>
                        <div class="col-md-3" id="newTaskBtn">
                            <button onclick="addNewTask()" class="btn btn-dark w-100">Add Task</button>
                        </div>
                        <div class="col-md-2" id="updateTaskBtn" style="display: none;">
                            <button onclick="updateTask()" class="btn btn-warning w-100">Update</button>
                        </div>
                        <div class="col-md-1" id="cancelBtn" style="display: none;">
                            <button onclick="cancelEdit()" class="btn btn-light w-100">X</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center g-3">
            <div class="col-md-8">
                <div class="table-span">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Task</th>
                                    <th>Created</th>
                                    <th>Expires</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tasklistBody">

                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
<script>
    var allTasks = []
    var idToUpdate = ''

    $(function() {
        getAllTasks()
    })

    function getAllTasks() {
        try {
            $.ajax({
                type: "GET",
                url: "/api/tasks",
                dataType: "json",
                success: function(response) {
                    allTasks = response
                    let Str = ''
                    response.forEach((x, index) => {
                        Str += `<tr>`
                        Str += `<th>${(index+1)}</th>`
                        Str += `<td>${x.title}</td>`
                        Str += `<td>${x.created}</td>`
                        Str += `<td>${x.expiryAt}</td>`
                        Str += `<td><button onclick="editTask(${x.id})" class="btn btn-link p-0 m-0 btn-sm text-warning text-decoration-none">Edit</button></td>`
                        Str += `<td><button onclick="removeTask(${x.id})" class="btn btn-link p-0 m-0 btn-sm text-danger text-decoration-none">Remove</button></td>`
                        Str += `</tr>`
                    });
                    if (response.length == 0) {
                        Str = '<tr><td class="text-center" colspan="6">No task, add a task.</td></tr>'
                        $('#tasklistBody').html(Str)
                    } else {
                        $('#tasklistBody').html(Str)
                    }
                }
            });
        } catch (error) {}
    }



    function addNewTask() {
        let title = $('#tasktitle').val()
        let expiry = $('#taskdate').val()
        if (title == '' || expiry == '') {
            alert('please complete fields')
            return;
        }

        let newTask = {
            title: $('#tasktitle').val(),
            expiry: (new Date(expiry)).toISOString()
        }

        try {
            $.ajax({
                type: "POST",
                url: "/api/tasks",
                dataType: "json",
                data: newTask,
                success: function(response) {
                    if (response == 1) {
                        $('#tasktitle').val('')
                        $('#taskdate').val('')
                        getAllTasks()
                    }
                }
            });
        } catch (error) {}
    }

    function removeTask(id) {
        try {
            $.ajax({
                type: "DELETE",
                url: "/api/tasks/" + id,
                dataType: "json",
                success: function(response) {
                    if (response == 1) {
                        getAllTasks()
                    }
                }
            });
        } catch (error) {}
    }

    function editTask(id) {
        let thisTask = allTasks.find(x => x.id == id)
        idToUpdate = id

        var date = new Date(thisTask.expiry);

        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();

        if (month < 10) month = "0" + month;
        if (day < 10) day = "0" + day;

        var today = year + "-" + month + "-" + day;

        $('#tasktitle').val(thisTask.title)
        $('#taskdate').val(today)

        $('#newTaskBtn').hide()
        $('#updateTaskBtn').show()
        $('#cancelBtn').show()
    }

    function cancelEdit() {
        $('#tasktitle').val('')
        $('#taskdate').val('')

        $('#newTaskBtn').show()
        $('#updateTaskBtn').hide()
        $('#cancelBtn').hide()
    }

    function updateTask() {

        let title = $('#tasktitle').val()
        let expiry = $('#taskdate').val()
        if (title == '' || expiry == '') {
            alert('please complete fields')
            return;
        }

        let Task = {
            title: $('#tasktitle').val(),
            expiry: (new Date(expiry)).toISOString()
        }

        try {
            $.ajax({
                type: "PUT",
                url: "/api/tasks/" + idToUpdate,
                dataType: "json",
                data: Task,
                success: function(response) {
                    if (response == 1) {
                        cancelEdit()
                        getAllTasks()
                    }
                }
            });
        } catch (error) {}
    }
</script>


</html>