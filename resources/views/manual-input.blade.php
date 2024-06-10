@extends('layouts.app')


@section('content')
<div class="d-flex">
    @include('menu')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Manual Input for Test Case</div>
                    <div class="card-body">
                        <form action="{{ route('generateDuskManual') }}" method="POST">
                            @csrf
                            <!-- Form fields for each property -->
                            <div class="form-group">
                                <label for="scenarioID">Scenario ID</label>
                                <input type="text" class="form-control" id="scenarioID" name="scenarioID" required>
                            </div>
                            <div class="form-group">
                                <label for="condition">Condition</label>
                                <input type="text" class="form-control" id="condition" name="condition" required>
                            </div>
                            <div class="form-group">
                                <label for="useCase">Use Case</label>
                                <input type="text" class="form-control" id="useCase" name="useCase" required>
                            </div>
                            <div class="form-group">
                                <label for="executionDetail">Execution Detail</label>
                                <input type="text" class="form-control" id="executionDetail" name="executionDetail" required>
                            </div>
                            <div class="form-group">
                                <label for="expectedResults">Expected Results</label>
                                <input type="text" class="form-control" id="expectedResults" name="expectedResults" required>
                            </div>
                            <div class="form-group">
                                <label for="locators">Locators</label>
                                <input type="text" class="form-control" id="locators" name="locators" required>
                            </div>
                            <div class="form-group">
                                <label for="inputData">Input Data</label>
                                <input type="text" class="form-control" id="inputData" name="inputData" required>
                            </div>
                            <br>
                            <button type="submit" class="btn btn-primary">Generate Test</button>
                        </form>

                        @if(session('code'))
                        <div class="alert alert-success mt-4">
                            <pre>{{ session('code') }}</pre>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection