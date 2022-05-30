@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">

                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From" class="form-control">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th width="300px">Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($products as $key => $product)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$product->title}} <br> Created at : {{ \Carbon\Carbon::parse($product->created_at)->format('d-M-yy') }}
                            </td>
                            <td><small>{{ $product->description }}</small></td>
                            <td>
                                <div style="height: 80px; overflow: hidden" id="variant">
                                    @foreach($product->productVariantPrices as $productVariantPrice)
                                        <dl class="row mb-0 pa-0">
                                            <dt class="col-sm-3 pb-0">
                                                <small>
                                                    <strong>{{$productVariantPrice['varientConbination']}}</strong>
                                                </small>
                                                {{--                                        SM/ Red/ V-Nick--}}
                                            </dt>
                                            <dd class="col-sm-9">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-6 pb-0">
                                                        <small>
                                                            <strong>Price : {{ number_format($productVariantPrice->price,2) }}</strong>
                                                        </small>
                                                    </dt>
                                                    <dd class="col-sm-6 pb-0">
                                                        <small>InStock : {{ number_format($productVariantPrice->stock,2) }}</small>
                                                    </dd>
                                                </dl>
                                            </dd>
                                        </dl>
                                    @endforeach
                                </div>
                                <button onclick="$('#variant').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('product.edit', 1) }}" class="btn btn-success">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing 1 to 10 out of 100</p>
                </div>
                <div class="col-md-2">
                    {{$products}}
                </div>
            </div>
        </div>
    </div>

@endsection
