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

        #progress-wrapper {
            margin-top: 20px;
            width: 100%;
            background-color: #f3f3f3;
            border-radius: 20px;
            overflow: hidden;
        }

        #progress-bar {
            width: 0%;
            height: 30px;
            background-color: #4caf50;
            text-align: center;
            color: white;
            line-height: 30px;
            border-radius: 20px;
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
                <input type="file" id="file" name="file">
                <div id="errros"></div>
            </div>
            <br><br>
            <button type="submit" class="submit-button">Upload</button>
        </form>
        <div id="progress-wrapper">
            <div id="progress-bar">0%</div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#fileUploadForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                var fileInput = document.getElementById('file');
                if (fileInput.files.length === 0) {

                    $("#errros").append(`<span class="text-danger">Please select a file before uploading.</span>`)

                } else {
                    $("#errros").empty()
                }

                $.ajax({
                    url: '{{ route("file.upload") }}',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    contentType: false,
                    processData: false,
                    success: function(response) {

                        if (response.success) {

                            $('#progress-bar').css('width', '100%');
                            $('#progress-bar').text('100%');
                            $('#messege_type').append(`<div class="alert alert-success">${response.message}</div>`);

                            setTimeout(function() {
                                window.location.reload();
                            }, 5000);

                        } else {
                            $('#message').text('File upload failed');
                        }
                    },
                    error: function() {
                        $('#message').text('An error occurred during file upload');
                    }
                });
            });
        });
    </script>

</body>

</html>