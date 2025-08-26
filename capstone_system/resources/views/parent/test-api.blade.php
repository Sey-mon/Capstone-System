@extends('layouts.dashboard')

@section('title', 'API Test')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">LLM API Test</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('parent.test-api') }}">
                        @csrf
                        <div class="form-group">
                            <label>Patient ID</label>
                            <input type="number" name="patient_id" class="form-control" value="1" required>
                        </div>
                        <div class="form-group">
                            <label>Available Foods</label>
                            <input type="text" name="available_foods" class="form-control" value="rice,chicken,vegetables" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Test API</button>
                    </form>
                    
                    @if(session('test_result'))
                        <div class="mt-4">
                            <h5>API Response:</h5>
                            <pre>{{ session('test_result') }}</pre>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="alert alert-danger mt-4">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
