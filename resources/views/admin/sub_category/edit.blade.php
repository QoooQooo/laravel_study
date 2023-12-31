@extends('admin.layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Sub Category</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('sub-category.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content">
    <!-- Default box -->
    <div class="container-fluid">
        <form action="" method="post" name="categoryForm" id="categoryForm">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name">Category</label>
                                <select name="category" id="category" class="form-control">
                                    <option value="">카테고리를 선택하세요</option>
                                    @if ($categories->isNotEmpty())
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ ($subCategory->category_id == $category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Name" value="{{ $subCategory->name }}">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="slug">Slug</label>
                                <input type="text" name="slug" id="slug" class="form-control" placeholder="Slug" value="{{ $subCategory->slug }}">
                                <p></p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option {{ ($subCategory->status == 1) ? 'selected' : '' }} value="1">사용</option>
                                    <option {{ ($subCategory->status == 0) ? 'selected' : '' }} value="0">사용안함</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status">노출여부</label>
                                <select name="showHome" id="showHome" class="form-control">
                                    <option {{ ($subCategory->showHome == 'Yes') ? 'selected' : '' }} value="Yes">노출</option>
                                    <option {{ ($subCategory->showHome == 'No') ? 'selected' : '' }} value="No">노출안함</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pb-5 pt-3">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('sub-category.index') }}" class="btn btn-outline-dark ml-3">Cancel</a>
            </div>
        </form>
    </div>
    <!-- /.card -->
</section>
<!-- /.content -->

@endsection

@section('customJs')
<script>
$("#categoryForm").submit(function(event){
    event.preventDefault();
    var element = $(this);
    $("button[type=submit]").prop('disabled', true);    //무슨의미인지 잘 모르겠음
    $.ajax({
        url: '{{ route("sub-category.update", $subCategory->id) }}',
        type: 'put',
        data: element.serializeArray(),
        dateType: 'json',
        success: function(response){
            $("button[type=submit]").prop('disabled', false);
            if(response["status"] == true) {

                window.location.href="{{ route('sub-category.index') }}";

                $("#name")
                .removeClass('is-invalid')
                .siblings('p')
                .removeClass('invalid-feedback')
                .html("");

                $("#slug")
                .removeClass('is-invalid')
                .siblings('p')
                .removeClass('invalid-feedback')
                .html("");

            } else {

                if (response['notFound'] == true) {
                    window.location.href="{{ route('sub-category.index') }}";
                    return false;
                }

                var errors = response['errors'];
                if(errors['name']) {
                    $("#name")
                    .addClass('is-invalid')
                    .siblings('p')
                    .addClass('invalid-feedback')
                    .html(errors['name']);
                } else {
                    $("#name")
                    .removeClass('is-invalid')
                    .siblings('p')
                    .removeClass('invalid-feedback')
                    .html("");
                }

                if(errors['slug']) {
                    $("#slug")
                    .addClass('is-invalid')
                    .siblings('p')
                    .addClass('invalid-feedback')
                    .html(errors['slug']);
                } else {
                    $("#slug")
                    .removeClass('is-invalid')
                    .siblings('p')
                    .removeClass('invalid-feedback')
                    .html("");
                }
            }
        }, error: function(jqXHR, exception){
            console.log("Something went wrong");
        }
    });
});

$("#name").change(function(){
    element = $(this);
    $("button[type=submit]").prop('disabled', true);    //무슨의미인지 잘 모르겠음
    $.ajax({
        url: '{{ route("getSlug") }}',
        type: 'get',
        data: {title: element.val()},
        dateType: 'json',
        success: function(response){
            $("button[type=submit]").prop('disabled', false);
            if (response["status"] == true) {
                $("#slug").val(response["slug"]);
            }
        }
    });
});

/* Dropzone.autoDiscover = false;
const dropzone = $("#image").dropzone({
    init: function() {
        this.on('addedfile', function(file) {
            if (this.files.length > 1) {
                this.removeFile(this.files[0]);
            }
        });
    },
    url: "{{ route('temp-images.create') }}",
    maxfiles: 1,
    paramName: 'image',
    addRemoveLinks: true,
    acceptedFiles: "image/jpeg, image/png, image/gif",
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }, success: function(file, response){
        $('#image_id').val(response.image_id);
        //console.log(response);
    }
}); */

</script>
@endsection
