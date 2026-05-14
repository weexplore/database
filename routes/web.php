<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\DestinationItemController;
use App\Http\Controllers\DestinationSourceController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\PlaceAliasController;
use App\Http\Controllers\TravellerController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\FuelStopController;
use App\Http\Controllers\TripLegController;
use App\Http\Controllers\TripStayController;
use App\Http\Controllers\TripItemController;
use App\Http\Controllers\TripBookingController;
use App\Http\Controllers\FuelPriceObservationController;
use App\Http\Controllers\TripFuelEstimateController;
use App\Http\Controllers\TripFuelPurchaseController;
use App\Http\Controllers\TripReviewController;
use App\Http\Controllers\TripReportController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\TripPlanItemController;

use App\Http\Controllers\BibleBookController;
use App\Http\Controllers\BibleVersionController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\InstrumentTypeController;
use App\Http\Controllers\InvestmentDashboardController;
use App\Http\Controllers\KnowledgeDomainController;
use App\Http\Controllers\KnowledgeTagController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ResearchDashboardController;


Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::prefix('places')->name('places.')->group(function () {
    Route::get('states-for-country', [PlaceController::class, 'statesForCountry'])->name('states-for-country');
    Route::get('regions-for-country-state', [PlaceController::class, 'regionsForCountryState'])->name('regions-for-country-state');
    Route::post('bulk-save', [PlaceController::class, 'bulkSave'])->name('bulk-save');
});

    Route::resource('attachments', AttachmentController::class)
        ->except(['show', 'create'])
        ->names('attachments')
        ->parameters([
            'attachments' => 'attachment',
        ]);

    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');

    Route::get('/attachments/{attachment}/view', [AttachmentController::class, 'view'])
        ->name('attachments.view');

Route::prefix('trips/{trip}')->group(function () {
    Route::get('/edit', [TripController::class, 'edit'])->name('trips.edit');
    Route::put('/', [TripController::class, 'update'])->name('trips.update');

    Route::prefix('planner')->name('trips.planner.')->group(function () {
        Route::get('/', [TripPlanItemController::class, 'index'])->name('index');
        Route::get('/create', [TripPlanItemController::class, 'create'])->name('create');
        Route::post('/', [TripPlanItemController::class, 'store'])->name('store');

        Route::get('/generate', [TripPlanItemController::class, 'generatePreview'])->name('generate');
        Route::post('/generate', [TripPlanItemController::class, 'generateApply'])->name('generate.apply');
        Route::post('/generated/rollback', [TripPlanItemController::class, 'rollbackGenerated'])->name('generated.rollback');

        Route::post('/resequence', [TripPlanItemController::class, 'resequence'])->name('resequence');
        Route::post('/bulk-update', [TripPlanItemController::class, 'bulkUpdate'])->name('bulk-update');
        Route::post('/renumber', [TripPlanItemController::class, 'renumber'])->name('renumber');
        Route::post('/bulk-add-destination-items', [TripPlanItemController::class, 'bulkAddDestinationItems'])
            ->name('bulk-add-destination-items');

        Route::get('/{tripPlanItem}/edit', [TripPlanItemController::class, 'edit'])->name('edit');
        Route::put('/{tripPlanItem}', [TripPlanItemController::class, 'update'])->name('update');
        Route::delete('/{tripPlanItem}', [TripPlanItemController::class, 'destroy'])->name('destroy');
    });

    // your existing legs/stays/items/bookings/etc here...
});

Route::get('/research', [ResearchDashboardController::class, 'index'])->name('research.index');
Route::get('/investments', [InvestmentDashboardController::class, 'index'])->name('investments.index');

Route::resource('knowledge-domains', KnowledgeDomainController::class)
    ->only(['index', 'destroy']);
Route::post('/knowledge-domains/bulk-save', [KnowledgeDomainController::class, 'bulkSave'])
    ->name('knowledge-domains.bulk-save');

Route::get('/knowledge-tags', [KnowledgeTagController::class, 'index'])->name('knowledge-tags.index');
Route::post('/knowledge-tags/bulk-save', [KnowledgeTagController::class, 'bulkSave'])->name('knowledge-tags.bulk-save');
Route::delete('/knowledge-tags/{knowledgeTag}', [KnowledgeTagController::class, 'destroy'])->name('knowledge-tags.destroy');

Route::get('/bible-versions', [BibleVersionController::class, 'index'])->name('bible-versions.index');
Route::post('/bible-versions/bulk-save', [BibleVersionController::class, 'bulkSave'])->name('bible-versions.bulk-save');
Route::delete('/bible-versions/{bibleVersion}', [BibleVersionController::class, 'destroy'])->name('bible-versions.destroy');

Route::get('/bible-books', [BibleBookController::class, 'index'])->name('bible-books.index');
Route::post('/bible-books/bulk-save', [BibleBookController::class, 'bulkSave'])->name('bible-books.bulk-save');
Route::delete('/bible-books/{bibleBook}', [BibleBookController::class, 'destroy'])->name('bible-books.destroy');

Route::get('/exchanges', [ExchangeController::class, 'index'])->name('exchanges.index');
Route::post('/exchanges/bulk-save', [ExchangeController::class, 'bulkSave'])->name('exchanges.bulk-save');
Route::delete('/exchanges/{exchange}', [ExchangeController::class, 'destroy'])->name('exchanges.destroy');

Route::get('/instrument-types', [InstrumentTypeController::class, 'index'])->name('instrument-types.index');
Route::post('/instrument-types/bulk-save', [InstrumentTypeController::class, 'bulkSave'])->name('instrument-types.bulk-save');
Route::delete('/instrument-types/{instrumentType}', [InstrumentTypeController::class, 'destroy'])->name('instrument-types.destroy');

Route::get('/portfolios', [PortfolioController::class, 'index'])->name('portfolios.index');
Route::post('/portfolios/bulk-save', [PortfolioController::class, 'bulkSave'])->name('portfolios.bulk-save');
Route::delete('/portfolios/{portfolio}', [PortfolioController::class, 'destroy'])->name('portfolios.destroy');



Route::resource('places', PlaceController::class)->except(['show']);

Route::resource('countries', CountryController::class);
Route::resource('states', StateController::class);
Route::resource('regions', RegionController::class);
Route::resource('places', PlaceController::class);
Route::resource('place-aliases', PlaceAliasController::class);
Route::resource('travellers', TravellerController::class);
Route::resource('trips', TripController::class);

// Trip traveller assignment helpers
Route::get('/travellers', [TravellerController::class, 'index'])->name('travellers.index');
Route::post('/travellers/bulk-save', [TravellerController::class, 'bulkSave'])->name('travellers.bulk-save');
Route::delete('/travellers/{traveller}', [TravellerController::class, 'destroy'])->name('travellers.destroy');

// Places assignment helpers
Route::get('/places', [PlaceController::class, 'index'])->name('places.index');
Route::post('/places/bulk-save', [PlaceController::class, 'bulkSave'])->name('places.bulk-save');
Route::delete('/places/{place}', [PlaceController::class, 'destroy'])->name('places.destroy');

Route::get('/places/{place}/edit', [PlaceController::class, 'edit'])->name('places.edit');
Route::put('/places/{place}', [PlaceController::class, 'update'])->name('places.update');

Route::get('/places/{place}/destinations/create-from-place', [DestinationController::class, 'createFromPlace'])
    ->name('places.destinations.create-from-place');


// Regions assignment helpers
Route::get('/regions', [RegionController::class, 'index'])->name('regions.index');
Route::post('/regions/bulk-save', [RegionController::class, 'bulkSave'])->name('regions.bulk-save');
Route::delete('/regions/{region}', [RegionController::class, 'destroy'])->name('regions.destroy');

Route::get('/countries', [CountryController::class, 'index'])->name('countries.index');
Route::post('/countries/bulk-save', [CountryController::class, 'bulkSave'])->name('countries.bulk-save');
Route::delete('/countries/{country}', [CountryController::class, 'destroy'])->name('countries.destroy');

Route::get('/states', [StateController::class, 'index'])->name('states.index');
Route::post('/states/bulk-save', [StateController::class, 'bulkSave'])->name('states.bulk-save');
Route::delete('/states/{state}', [StateController::class, 'destroy'])->name('states.destroy');

Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
Route::post('/trips/bulk-save', [TripController::class, 'bulkSave'])->name('trips.bulk-save');
Route::get('/trips/{trip}/edit', [TripController::class, 'edit'])->name('trips.edit');
Route::put('/trips/{trip}', [TripController::class, 'update'])->name('trips.update');
Route::delete('/trips/{trip}', [TripController::class, 'destroy'])->name('trips.destroy');

Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
Route::post('/destinations/bulk-save', [DestinationController::class, 'bulkSave'])->name('destinations.bulk-save');
Route::get('/destinations/{destination}/edit', [DestinationController::class, 'edit'])->name('destinations.edit');
Route::put('/destinations/{destination}', [DestinationController::class, 'update'])->name('destinations.update');
Route::delete('/destinations/{destination}', [DestinationController::class, 'destroy'])->name('destinations.destroy');

Route::get('/reports/places/reference-book', [PlaceController::class, 'referenceBook'])
    ->name('reports.places.reference-book');

Route::resource('fuel-stops', FuelStopController::class)->except(['show']);
Route::resource('fuel-price-observations', FuelPriceObservationController::class)->except(['show']);

    Route::resource('vehicles', VehicleController::class)->except(['show']);

    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::post('/vehicles/bulk-save', [VehicleController::class, 'bulkSave'])->name('vehicles.bulk-save');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

Route::prefix('trips/{trip}')->group(function () {
    Route::resource('fuel-estimates', TripFuelEstimateController::class)
        ->names('trips.fuel-estimates')
        ->parameters([
            'fuel-estimates' => 'fuelEstimate',
        ]);

    Route::resource('fuel-purchases', TripFuelPurchaseController::class)
        ->names('trips.fuel-purchases');
});

// routes/web.php
Route::post('/destinations/{destination}/suggest-from-web', [DestinationController::class, 'suggestFromWeb'])
    ->name('destinations.suggest-from-web');

Route::post('/destination-sources', [DestinationSourceController::class, 'store'])
    ->name('destination-sources.store');

Route::put('/destination-sources/{destinationsource}', [DestinationSourceController::class, 'update'])
    ->name('destination-sources.update');

Route::delete('/destination-sources/{destinationsource}', [DestinationSourceController::class, 'destroy'])
    ->name('destination-sources.destroy');

Route::get('/destination-items', [DestinationItemController::class, 'index'])->name('destination-items.index');
Route::get('/destination-items/create', [DestinationItemController::class, 'create'])->name('destination-items.create');
Route::post('/destination-items', [DestinationItemController::class, 'store'])->name('destination-items.store');
Route::get('/destination-items/{destinationItem}/edit', [DestinationItemController::class, 'edit'])->name('destination-items.edit');
Route::put('/destination-items/{destinationItem}', [DestinationItemController::class, 'update'])->name('destination-items.update');
Route::delete('/destination-items/{destinationItem}', [DestinationItemController::class, 'destroy'])->name('destination-items.destroy');
Route::get(
    '/destinations/{destination}/destination-items/create-from-destination',
    [DestinationItemController::class, 'createFromDestination']
)->name('destination-items.create-from-destination');

Route::prefix('trips/{trip}')->name('trips.')->group(function () {
    Route::get('/edit', [TripController::class, 'edit'])->name('edit');
    Route::put('/', [TripController::class, 'update'])->name('update');
    
     // Trip Book report (one trip at a time)
    Route::get('/book', [TripReportController::class, 'book'])
        ->name('book');

    Route::get('/legs', [TripLegController::class, 'index'])->name('legs.index');
    Route::get('/legs/create', [TripLegController::class, 'create'])->name('legs.create');
    Route::post('/legs', [TripLegController::class, 'store'])->name('legs.store');
    Route::get('/legs/{tripLeg}/edit', [TripLegController::class, 'edit'])->name('legs.edit');
    Route::put('/legs/{tripLeg}', [TripLegController::class, 'update'])->name('legs.update');
    Route::delete('/legs/{tripLeg}', [TripLegController::class, 'destroy'])->name('legs.destroy');

    Route::get('/stays', [TripStayController::class, 'index'])->name('stays.index');
    Route::get('/stays/create', [TripStayController::class, 'create'])->name('stays.create');
    Route::post('/stays', [TripStayController::class, 'store'])->name('stays.store');
    Route::get('/stays/{tripStay}/edit', [TripStayController::class, 'edit'])->name('stays.edit');
    Route::put('/stays/{tripStay}', [TripStayController::class, 'update'])->name('stays.update');
    Route::delete('/stays/{tripStay}', [TripStayController::class, 'destroy'])->name('stays.destroy');
    Route::post('/stays/prefill-from-place', [TripStayController::class, 'prefillFromPlace'])
        ->name('stays.prefill-from-place');

    Route::post('/stays/prefill-from-previous-stay', [TripStayController::class, 'prefillFromPreviousStay'])
        ->name('stays.prefill-from-previous-stay');

    Route::get('/items', [TripItemController::class, 'index'])->name('items.index');
    Route::get('/items/create', [TripItemController::class, 'create'])->name('items.create');
    Route::post('/items', [TripItemController::class, 'store'])->name('items.store');
    Route::get('/items/{tripItem}/edit', [TripItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{tripItem}', [TripItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{tripItem}', [TripItemController::class, 'destroy'])->name('items.destroy');

    Route::resource('bookings', TripBookingController::class)
        ->except(['create', 'show'])
        ->names('bookings')
        ->parameters([
            'bookings' => 'booking',
        ]);
    Route::resource('reviews', TripReviewController::class)
        ->except(['show', 'create'])
        ->names('reviews')
        ->parameters([
            'reviews' => 'review',
        ]);

      

   
});