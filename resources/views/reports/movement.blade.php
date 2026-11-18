@extends('layouts.app')
@section('title', $reportData['title'])
@section('content')
@include('reports._database-report')
@endsection