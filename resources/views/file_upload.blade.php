<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>File Upload with Progress Bar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .upload-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-input-wrapper input[type="file"] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
        }

        .file-button {
            background-color: #4caf50;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .file-button:hover {
            background-color: #45a049;
        }

        .submit-button {
            background-color: #008cba;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        .submit-button:hover {
            background-color: #007bb5;
        }
    </style>
</head>

<body>
    <div class="upload-container">
        <div id="messege_type"></div>
        <h5>File Upload with Progress Bar</h5>
        <form id="fileUploadForm" enctype="multipart/form-data">
            <div class="file-input-wrapper mt-4">
                <button class="file-button">Choose File</button>
                <input type="file" id="file" class="form-control" name="file">
                <div id="file-name"></div>
                <div id="errros" style="color:red;"></div>
            </div>
            <br><br>
            <button type="submit" class="submit-button">Upload</button>
        </form>
        <div class="progress mt-3" style="display:none">
            <div id="progress-bar" class="progress-bar progress-bar-striped bg-info progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>


    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            $('.file-button').on('click', function() {
                $('#file').click();
            });
            $('#file').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $('#errros').text('');
                    $('#file-name').text('Selected File: ' + fileName);

                }
            });
            $('#fileUploadForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: '{{ route("file.upload") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {

                        $('.progress').show();
                        if (response.success) {
                            $('#file-name').text('');
                            $('#fileUploadForm')[0].reset();


                            let progress = 0;
                            let interval = setInterval(function() {
                                if (progress <= 100) {
                                    updateProgressBar(progress);
                                    progress++;
                                } else {
                                    clearInterval(interval);
                                    $('#messege_type').append(
                                        `<div class="alert alert-success">${response.message}</div>`
                                    );
                                }
                            }, 20);


                            setTimeout(function() {
                                window.location.reload();
                            }, 5000);

                        } else {
                            $('#message').text('File upload failed');
                        }
                    },
                    error: function(response) {

                        console.log(response.responseJSON.errors.file[0]);

                        $('#errros').text(response.responseJSON.errors.file[0]);
                    }
                });
            });

        });

        function updateProgressBar(value) {
            $('#progress-bar').css('width', value + '%').attr('aria-valuenow', value);
            $('#progress-bar').text(value + '%');
        }
    </script>

</body>

</html>