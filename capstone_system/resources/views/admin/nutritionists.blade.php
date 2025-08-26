@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>Nutritionist Applications</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>License #</th>
                <th>Years Experience</th>
                <th>Qualifications</th>
                <th>Professional ID</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nutritionists as $nutritionist)
            <tr>
                <td>{{ $nutritionist->first_name }} {{ $nutritionist->last_name }}</td>
                <td>{{ $nutritionist->email }}</td>
                <td>{{ $nutritionist->license_number }}</td>
                <td>{{ $nutritionist->years_experience }}</td>
                <td>{{ $nutritionist->qualifications }}</td>
                <td>
                    @if($nutritionist->professional_id_path)
                        <a href="{{ route('admin.nutritionist.professional_id', $nutritionist->user_id) }}" target="_blank">View</a>
                    @else
                        <span class="text-muted">No file</span>
                    @endif
                </td>
                <td>{{ ucfirst($nutritionist->verification_status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
