@extends('layouts.app')
@section('title', 'Register Visitor')
@section('content')
<x-page-heading title="Register Visitor" description="Create a visitor identity within your authorized facility." :breadcrumbs="[['label'=>'Visitors','route'=>'visitors.index'],['label'=>'Register']]"/>
@include('visitors._identity-form')
@endsection