@extends('layouts.app')

@section('content')

<div class="container d-flex justify-content-center py-5">

    <div class="row">
        <p class="text-center">{{ __('messages.UpdateFoodGoalDescription') }}</p>

        <form method="POST" autocomplete="off">
            @csrf
            <table class="table mt-3">
                <thead>
                    <tr>
                        <th scope="col">{{ __('messages.Name') }}</th>
                        <th scope="col">{{ __('messages.AmountInGrams') }}</th>
                        <th scope="col">{{ __('messages.Calories') }}</th>
                        <th scope="col">{{ __('messages.Carbohydrate') }}</th>
                        <th scope="col">{{ __('messages.Protein') }}</th>
                        <th scope="col">{{ __('messages.Fat') }}</th>
                        <th scope="col">{{ __('messages.SaturatedFat') }}</th>
                        <th scope="col">{{ __('messages.TransFat') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="text" class="form-control bg-light" name="name" value="{{ $food->name }}" step="any" readonly>
                        <td>
                            <div class="input-group mb-3">
                                <input id="quantityGramsId-1" type="number" class="form-control" name="quantity_grams" value="{{ $food->quantity_grams }}" step="any">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">g</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="input-group mb-3">
                                <input id="quantityCalorieId-1" type="number" class="form-control" name="calories" value="{{ $food->calories }}" step="any">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">Kcal</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="input-group mb-3">
                                <input id="quantityCarbohydrateId-1" type="number" class="form-control" name="carbohydrate" value="{{ $food->carbohydrate }}" step="any">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">g</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="input-group mb-3">
                                <input id="quantityProteinId-1" type="number" class="form-control" name="protein" value="{{ $food->protein }}" step="any">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">g</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="input-group mb-3">
                                <input id="quantityTotalFatId-1" type="number" class="form-control" name="total_fat" value="{{ $food->total_fat }}" step="any">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">g</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="input-group mb-3">
                                <input id="quantitySaturatedFatId-1" type="number" class="form-control" name="saturated_fat" value="{{ $food->saturated_fat }}" step="any">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">g</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="input-group mb-3">
                                <input id="quantityTransFatId-1" type="number" class="form-control" name="trans_fat" value="{{ $food->trans_fat }}" step="any">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">g</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr class="col-lg-12">

            <small class="col-lg-12 text-justify">
                {{ __('messages.AddFoodAlert') }}
            </small>

            <hr class="col-lg-12">

            <div class="col-lg-12 mt-2">
                <button type="submit" class="btn btn-primary col-lg-12">
                    {{ __('messages.Update') }}
                </button>
            </div>
        </form>
    </div>

</div>

<script src="{{ asset('js/goals.js') }}"></script>

@endsection