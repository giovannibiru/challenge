<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Challenge</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

</head>
<body>
    <div class="d-flex justify-content-center p-5 mb-4 bg-light">
        <h3>Interview Challenge</h3> 
    </div>

    <div class="container">
        @if (session('status'))
            <div class="alert alert-dark" role="alert">
                {{ session('status') }}
            </div>
        @endif
        
        <form action="{{ route('uploadFile') }}" method="POST" enctype='multipart/form-data'>
            @csrf

            <div class="form-group">
                <label for="file" class="form-label">Default Input File</label>
                <input class="form-control {{ $errors->has('file') ? 'is-invalid' : '' }}" type="file" id="file" name="file">
                @error('file')
                    <span class="text-danger">
                        <small><b>{{ $message }}</b></small>
                    </span>
                @enderror
            </div>
            <button type="submit" class="btn btn-sm btn-success mt-4">Upload!</button>
        </form>
        
    </div>
</body>
</html>