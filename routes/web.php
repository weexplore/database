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
use App\Http\Controllers\KnowledgeFamilyTreeReportController;

use App\Http\Controllers\BibleBookController;
use App\Http\Controllers\BibleReferenceController;
use App\Http\Controllers\BibleVersionController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\InstrumentTypeController;
use App\Http\Controllers\KnowledgeCategoryController;
use App\Http\Controllers\KnowledgeDomainController;
use App\Http\Controllers\KnowledgeItemController;
use App\Http\Controllers\KnowledgeItemTypeController;
use App\Http\Controllers\KnowledgeSearchController;
use App\Http\Controllers\KnowledgeTagController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\KnowledgeItemNoteController;
use App\Http\Controllers\KnowledgeItemSourceController;
use App\Http\Controllers\KnowledgeItemReviewLogController;
use App\Http\Controllers\KnowledgeItemAttachmentController;
use App\Http\Controllers\KnowledgeItemRelationshipController;
use App\Http\Controllers\KnowledgePersonFactController;
use App\Http\Controllers\KnowledgeRelationshipFactController;
use App\Http\Controllers\KnowledgeAttachmentController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\InstrumentAliasController;
use App\Http\Controllers\InstrumentPriceObservationController;
use App\Http\Controllers\InstrumentCorporateActionController;
use App\Http\Controllers\InstrumentTransactionController;
use App\Http\Controllers\KnowledgeReportController;

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
| Main application landing page.
*/
Route::get('/', [AdminDashboardController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Dashboards
|--------------------------------------------------------------------------
| Secondary top-level dashboard areas.
*/
Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.index');

/*
|--------------------------------------------------------------------------
| Attachments
|--------------------------------------------------------------------------
| Shared attachment CRUD plus download/view helpers.
*/
Route::prefix('attachments')->name('attachments.')->group(function () {
    Route::get('{attachment}/download', [AttachmentController::class, 'download'])->name('download');
    Route::get('{attachment}/view', [AttachmentController::class, 'view'])->name('view');
});

Route::resource('attachments', AttachmentController::class)
    ->except(['show', 'create'])
    ->names('attachments')
    ->parameters([
        'attachments' => 'attachment',
    ]);

/*
|--------------------------------------------------------------------------
| Geography and master registers
|--------------------------------------------------------------------------
| Country, state, region, place, traveller, vehicle and related registers.
| These are structured as compact master-list style modules.
*/
Route::prefix('countries')->name('countries.')->group(function () {
    Route::get('/', [CountryController::class, 'index'])->name('index');
    Route::post('bulk-save', [CountryController::class, 'bulkSave'])->name('bulk-save');
    Route::delete('{country}', [CountryController::class, 'destroy'])->name('destroy');
});

Route::prefix('states')->name('states.')->group(function () {
    Route::get('/', [StateController::class, 'index'])->name('index');
    Route::post('bulk-save', [StateController::class, 'bulkSave'])->name('bulk-save');
    Route::delete('{state}', [StateController::class, 'destroy'])->name('destroy');
});

Route::prefix('regions')->name('regions.')->group(function () {
    Route::get('/', [RegionController::class, 'index'])->name('index');
    Route::post('bulk-save', [RegionController::class, 'bulkSave'])->name('bulk-save');
    Route::delete('{region}', [RegionController::class, 'destroy'])->name('destroy');
});

Route::prefix('places')->name('places.')->group(function () {
    Route::get('/', [PlaceController::class, 'index'])->name('index');
    Route::post('bulk-save', [PlaceController::class, 'bulkSave'])->name('bulk-save');
    Route::get('states-for-country', [PlaceController::class, 'statesForCountry'])->name('states-for-country');
    Route::get('regions-for-country-state', [PlaceController::class, 'regionsForCountryState'])->name('regions-for-country-state');
    Route::get('create', [PlaceController::class, 'create'])->name('create');
    Route::post('/', [PlaceController::class, 'store'])->name('store');
    Route::get('{place}/edit', [PlaceController::class, 'edit'])->name('edit');
    Route::put('{place}', [PlaceController::class, 'update'])->name('update');
    Route::delete('{place}', [PlaceController::class, 'destroy'])->name('destroy');
    Route::get('{place}/destinations/create-from-place', [DestinationController::class, 'createFromPlace'])
        ->name('destinations.create-from-place');
});

Route::resource('place-aliases', PlaceAliasController::class);

Route::prefix('travellers')->name('travellers.')->group(function () {
    Route::get('/', [TravellerController::class, 'index'])->name('index');
    Route::post('bulk-save', [TravellerController::class, 'bulkSave'])->name('bulk-save');
    Route::get('create', [TravellerController::class, 'create'])->name('create');
    Route::post('/', [TravellerController::class, 'store'])->name('store');
    Route::get('{traveller}/edit', [TravellerController::class, 'edit'])->name('edit');
    Route::put('{traveller}', [TravellerController::class, 'update'])->name('update');
    Route::delete('{traveller}', [TravellerController::class, 'destroy'])->name('destroy');
});

Route::prefix('vehicles')->name('vehicles.')->group(function () {
    Route::get('/', [VehicleController::class, 'index'])->name('index');
    Route::post('bulk-save', [VehicleController::class, 'bulkSave'])->name('bulk-save');
    Route::get('create', [VehicleController::class, 'create'])->name('create');
    Route::post('/', [VehicleController::class, 'store'])->name('store');
    Route::get('{vehicle}/edit', [VehicleController::class, 'edit'])->name('edit');
    Route::put('{vehicle}', [VehicleController::class, 'update'])->name('update');
    Route::delete('{vehicle}', [VehicleController::class, 'destroy'])->name('destroy');
});

Route::resource('fuel-stops', FuelStopController::class)->except(['show']);
Route::resource('fuel-price-observations', FuelPriceObservationController::class)->except(['show']);

/*
|--------------------------------------------------------------------------
| Destinations
|--------------------------------------------------------------------------
| Curated destination content, sources, and destination items.
*/
Route::prefix('destinations')->name('destinations.')->group(function () {
    Route::get('/', [DestinationController::class, 'index'])->name('index');
    Route::post('bulk-save', [DestinationController::class, 'bulkSave'])->name('bulk-save');
    Route::get('{destination}/edit', [DestinationController::class, 'edit'])->name('edit');
    Route::put('{destination}', [DestinationController::class, 'update'])->name('update');
    Route::delete('{destination}', [DestinationController::class, 'destroy'])->name('destroy');
    Route::post('{destination}/suggest-from-web', [DestinationController::class, 'suggestFromWeb'])->name('suggest-from-web');
    Route::get('{destination}/destination-items/create-from-destination', [DestinationItemController::class, 'createFromDestination'])
        ->name('destination-items.create-from-destination');
});

Route::prefix('destination-sources')->name('destination-sources.')->group(function () {
    Route::post('/', [DestinationSourceController::class, 'store'])->name('store');
    Route::put('{destinationsource}', [DestinationSourceController::class, 'update'])->name('update');
    Route::delete('{destinationsource}', [DestinationSourceController::class, 'destroy'])->name('destroy');
});

Route::prefix('destination-items')->name('destination-items.')->group(function () {
    Route::get('/', [DestinationItemController::class, 'index'])->name('index');
    Route::get('create', [DestinationItemController::class, 'create'])->name('create');
    Route::post('/', [DestinationItemController::class, 'store'])->name('store');
    Route::get('{destinationItem}/edit', [DestinationItemController::class, 'edit'])->name('edit');
    Route::put('{destinationItem}', [DestinationItemController::class, 'update'])->name('update');
    Route::delete('{destinationItem}', [DestinationItemController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
| Shared reports outside a single module workflow.
*/
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('places/reference-book', [PlaceController::class, 'referenceBook'])->name('places.reference-book');

    Route::get('knowledge/categories/reference-book', [KnowledgeReportController::class, 'categoryReferenceBook'])
        ->name('knowledge.categories.reference-book');
    Route::get('knowledge/domains/reference-book', [KnowledgeReportController::class, 'domainReferenceBook'])
        ->name('knowledge.domains.reference-book');
    Route::get('knowledge/categories/tree-reference-book', [KnowledgeReportController::class, 'categoryTreeReferenceBook'])
        ->name('knowledge.categories.tree-reference-book');
    Route::get('knowledge/family-tree', [KnowledgeFamilyTreeReportController::class, 'show'])
        ->name('knowledge.family-tree');
    Route::get('knowledge/items/{knowledgeItem}/reference-book',[KnowledgeReportController::class, 'knowledgeItemReferenceBook'])->name('knowledge.items.reference-book');
});

/*
|--------------------------------------------------------------------------
| Trips
|--------------------------------------------------------------------------
| Trip register plus full nested workflow: planner, legs, stays, items,
| bookings, reviews, fuel estimates, fuel purchases, and trip book.
*/
Route::prefix('trips')->name('trips.')->group(function () {
    Route::get('/', [TripController::class, 'index'])->name('index');
    Route::post('bulk-save', [TripController::class, 'bulkSave'])->name('bulk-save');
    Route::get('create', [TripController::class, 'create'])->name('create');
    Route::post('/', [TripController::class, 'store'])->name('store');
    Route::get('{trip}/edit', [TripController::class, 'edit'])->name('edit');
    Route::put('{trip}', [TripController::class, 'update'])->name('update');
    Route::delete('{trip}', [TripController::class, 'destroy'])->name('destroy');
    Route::get('{trip}/book', [TripReportController::class, 'book'])->name('book');

    Route::prefix('{trip}')->group(function () {
        /*
        |------------------------------------------------------------------
        | Trip planner
        |------------------------------------------------------------------
        | Planning layer before operational trip records are created.
        */
        Route::prefix('planner')->name('planner.')->group(function () {
            Route::get('/', [TripPlanItemController::class, 'index'])->name('index');
            Route::get('create', [TripPlanItemController::class, 'create'])->name('create');
            Route::post('/', [TripPlanItemController::class, 'store'])->name('store');
            Route::get('generate', [TripPlanItemController::class, 'generatePreview'])->name('generate');
            Route::post('generate', [TripPlanItemController::class, 'generateApply'])->name('generate.apply');
            Route::post('generated/rollback', [TripPlanItemController::class, 'rollbackGenerated'])->name('generated.rollback');
            Route::post('resequence', [TripPlanItemController::class, 'resequence'])->name('resequence');
            Route::post('bulk-update', [TripPlanItemController::class, 'bulkUpdate'])->name('bulk-update');
            Route::post('renumber', [TripPlanItemController::class, 'renumber'])->name('renumber');
            Route::post('reorder', [TripPlanItemController::class, 'reorder'])->name('reorder');
            Route::post('bulk-add-destination-items', [TripPlanItemController::class, 'bulkAddDestinationItems'])
                ->name('bulk-add-destination-items');
            Route::get('{tripPlanItem}/edit', [TripPlanItemController::class, 'edit'])->name('edit');
            Route::put('{tripPlanItem}', [TripPlanItemController::class, 'update'])->name('update');
            Route::delete('{tripPlanItem}', [TripPlanItemController::class, 'destroy'])->name('destroy');
            
        });

        /*
        |------------------------------------------------------------------
        | Trip movement and stay workflow
        |------------------------------------------------------------------
        */
        Route::resource('legs', TripLegController::class)
            ->names('legs')
            ->parameters(['legs' => 'tripLeg']);
        Route::post('legs/reorder', [TripLegController::class, 'reorder'])
            ->name('legs.reorder');

        Route::resource('stays', TripStayController::class)
            ->names('stays')
            ->parameters(['stays' => 'tripStay']);
        Route::post('stays/prefill-from-place', [TripStayController::class, 'prefillFromPlace'])
            ->name('stays.prefill-from-place');
        Route::post('stays/prefill-from-previous-stay', [TripStayController::class, 'prefillFromPreviousStay'])
            ->name('stays.prefill-from-previous-stay');

        /*
        |------------------------------------------------------------------
        | Trip content and transactions
        |------------------------------------------------------------------
        */
        Route::resource('items', TripItemController::class)
            ->names('items')
            ->parameters(['items' => 'tripItem']);

        Route::resource('bookings', TripBookingController::class)
            ->except(['create', 'show'])
            ->names('bookings')
            ->parameters(['bookings' => 'booking']);

        Route::resource('reviews', TripReviewController::class)
            ->except(['show', 'create'])
            ->names('reviews')
            ->parameters(['reviews' => 'review']);

        Route::resource('fuel-estimates', TripFuelEstimateController::class)
            ->names('fuel-estimates')
            ->parameters(['fuel-estimates' => 'fuelEstimate']);

        Route::resource('fuel-purchases', TripFuelPurchaseController::class)
            ->names('fuel-purchases');
    });
});

/*
|--------------------------------------------------------------------------
| Knowledge reference registers
|--------------------------------------------------------------------------
| Knowledge-side master registers and supporting lookup data.
*/
Route::prefix('knowledge-domains')->name('knowledge-domains.')->group(function () {
    Route::get('/', [KnowledgeDomainController::class, 'index'])->name('index');
    Route::post('bulk-save', [KnowledgeDomainController::class, 'bulkSave'])->name('bulk-save');
    Route::delete('{knowledgeDomain}', [KnowledgeDomainController::class, 'destroy'])->name('destroy');
});

Route::prefix('knowledge-tags')->name('knowledge-tags.')->group(function () {
    Route::get('/', [KnowledgeTagController::class, 'index'])->name('index');
    Route::post('bulk-save', [KnowledgeTagController::class, 'bulkSave'])->name('bulk-save');
    Route::delete('{knowledgeTag}', [KnowledgeTagController::class, 'destroy'])->name('destroy');
});

Route::prefix('knowledge-categories')->name('knowledge-categories.')->group(function () {
    Route::get('/', [KnowledgeCategoryController::class, 'index'])->name('index');
    Route::post('bulk-save', [KnowledgeCategoryController::class, 'bulkSave'])->name('bulk-save');
    Route::get('create', [KnowledgeCategoryController::class, 'create'])->name('create');
    Route::post('/', [KnowledgeCategoryController::class, 'store'])->name('store');
    Route::put('{knowledgeCategory}', [KnowledgeCategoryController::class, 'update'])->name('update');
    Route::delete('{knowledgeCategory}', [KnowledgeCategoryController::class, 'destroy'])->name('destroy');
});

Route::prefix('knowledge-item-types')->name('knowledge.item-types.')->group(function () {
    Route::get('/', [KnowledgeItemTypeController::class, 'index'])->name('index');
    Route::post('bulk-save', [KnowledgeItemTypeController::class, 'bulkSave'])->name('bulk-save');
    Route::delete('{knowledgeItemType}', [KnowledgeItemTypeController::class, 'destroy'])->name('destroy');
});

Route::prefix('bible-versions')->name('bible-versions.')->group(function () {
    Route::get('/', [BibleVersionController::class, 'index'])->name('index');
    Route::post('bulk-save', [BibleVersionController::class, 'bulkSave'])->name('bulk-save');
    Route::delete('{bibleVersion}', [BibleVersionController::class, 'destroy'])->name('destroy');
});

Route::prefix('bible-books')->name('bible-books.')->group(function () {
    Route::get('/', [BibleBookController::class, 'index'])->name('index');
    Route::post('bulk-save', [BibleBookController::class, 'bulkSave'])->name('bulk-save');
    Route::delete('{bibleBook}', [BibleBookController::class, 'destroy'])->name('destroy');
});

Route::prefix('exchanges')->name('exchanges.')->group(function () {
    Route::get('/', [ExchangeController::class, 'index'])->name('index');
    Route::post('bulk-save', [ExchangeController::class, 'bulkSave'])->name('bulk-save');
    Route::delete('{exchange}', [ExchangeController::class, 'destroy'])->name('destroy');
});

Route::prefix('instrument-types')->name('instrument-types.')->group(function () {
    Route::get('/', [InstrumentTypeController::class, 'index'])->name('index');
    Route::post('bulk-save', [InstrumentTypeController::class, 'bulkSave'])->name('bulk-save');
    Route::delete('{instrumentType}', [InstrumentTypeController::class, 'destroy'])->name('destroy');
});

Route::prefix('portfolios')->name('portfolios.')->group(function () {
    Route::get('/', [PortfolioController::class, 'index'])->name('index');
    Route::post('bulk-save', [PortfolioController::class, 'bulkSave'])->name('bulk-save');
    Route::delete('{portfolio}', [PortfolioController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Knowledge
|--------------------------------------------------------------------------
| Knowledge item workflow and nested item sub-resources.
*/
Route::prefix('knowledge')->name('knowledge.')->group(function () {
    Route::get('/search', [KnowledgeSearchController::class, 'index'])
            ->name('search');
    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/', [KnowledgeItemController::class, 'index'])->name('index');
        Route::post('bulk-save', [KnowledgeItemController::class, 'bulkSave'])->name('bulk-save');
        Route::get('{knowledgeItem}/edit', [KnowledgeItemController::class, 'edit'])->name('edit');
        Route::put('{knowledgeItem}', [KnowledgeItemController::class, 'update'])->name('update');
        Route::delete('{knowledgeItem}', [KnowledgeItemController::class, 'destroy'])->name('destroy');
        Route::post('{knowledgeItem}/person-facts', [KnowledgePersonFactController::class, 'store'])->name('person-facts.store');
        Route::get('{knowledgeItem}/person-facts/{knowledgePersonFact}/edit', [KnowledgePersonFactController::class, 'edit'])
            ->name('person-facts.edit');

        Route::put('{knowledgeItem}/person-facts/{knowledgePersonFact}', [KnowledgePersonFactController::class, 'update'])
            ->name('person-facts.update');

        Route::delete('{knowledgeItem}/person-facts/{knowledgePersonFact}', [KnowledgePersonFactController::class, 'destroy'])
            ->name('person-facts.destroy');

        Route::post('{knowledgeItem}/relationships/{knowledgeRelationship}/facts', [KnowledgeRelationshipFactController::class, 'store'])
            ->name('relationship-facts.store');

        Route::get('{knowledgeItem}/relationships/{knowledgeRelationship}/facts/{knowledgeRelationshipFact}/edit', [KnowledgeRelationshipFactController::class, 'edit'])
            ->name('relationship-facts.edit');

        Route::put('{knowledgeItem}/relationships/{knowledgeRelationship}/facts/{knowledgeRelationshipFact}', [KnowledgeRelationshipFactController::class, 'update'])
            ->name('relationship-facts.update');

        Route::delete('{knowledgeItem}/relationships/{knowledgeRelationship}/facts/{knowledgeRelationshipFact}', [KnowledgeRelationshipFactController::class, 'destroy'])
            ->name('relationship-facts.destroy');


        Route::post('{knowledgeItem}/notes/reorder', [KnowledgeItemNoteController::class, 'reorder'])->name('notes.reorder');   
        Route::post('{knowledgeItem}/person-facts/reorder', [KnowledgePersonFactController::class, 'reorder'])->name('person-facts.reorder');
        Route::post('{knowledgeItem}/person-facts/reorder/debug', function () {
            return 'debug-route-hit';
        })->name('person-facts.reorder-debug');
        Route::post('{knowledgeItem}/relationships/{knowledgeRelationship}/facts/reorder', [KnowledgeRelationshipFactController::class, 'reorder'])->name('relationship-facts.reorder'); 

        Route::resource('{knowledgeItem}/notes', KnowledgeItemNoteController::class)
            ->except(['index', 'show', 'create'])
            ->parameters([
                'notes' => 'knowledgeNote',
            ]);

        Route::resource('{knowledgeItem}/sources', KnowledgeItemSourceController::class)
            ->except(['index', 'show', 'create'])
            ->parameters([
                'sources' => 'knowledgeSource',
            ]);

        Route::post('{knowledgeItem}/sources/fetch', [KnowledgeItemSourceController::class, 'fetchFromInternet'])
            ->name('sources.fetch');

        Route::resource('{knowledgeItem}/review-logs', KnowledgeItemReviewLogController::class)
            ->except(['index', 'show', 'create'])
            ->parameters([
                'review-logs' => 'knowledgeReviewLog',
            ]);

        Route::post('{knowledgeItem}/relationships/reorder', [KnowledgeItemRelationshipController::class, 'reorder'])->name('relationships.reorder');

        Route::post('{knowledgeItem}/relationships/reorder', [KnowledgeItemRelationshipController::class, 'reorder'])
            ->name('relationships.reorder');

        Route::resource('{knowledgeItem}/relationships', KnowledgeItemRelationshipController::class)
            ->except(['index', 'show', 'create'])
            ->parameters([
                'relationships' => 'knowledgeRelationship',
            ]);

        Route::post('{knowledgeItem}/bible-references', [BibleReferenceController::class, 'store'])
            ->name('bible-references.store');

        Route::post('{knowledgeItem}/instrument', [InstrumentController::class, 'storeForKnowledgeItem'])
            ->name('instrument.store');

        Route::put('{knowledgeItem}/instrument/{instrument}', [InstrumentController::class, 'updateForKnowledgeItem'])
            ->name('instrument.update');

        Route::post('{knowledgeItem}/instrument/{instrument}/aliases', [InstrumentAliasController::class, 'storeForInstrument'])
            ->name('instrument.aliases.store');

        Route::put('{knowledgeItem}/instrument/{instrument}/aliases/{alias}', [InstrumentAliasController::class, 'updateForInstrument'])
            ->name('instrument.aliases.update');

        Route::delete('{knowledgeItem}/instrument/{instrument}/aliases/{alias}', [InstrumentAliasController::class, 'destroyForInstrument'])
            ->name('instrument.aliases.destroy');

        Route::post('{knowledgeItem}/instrument/{instrument}/price-observations', [InstrumentPriceObservationController::class, 'storeForInstrument'])
            ->name('instrument.price-observations.store');

        Route::put('{knowledgeItem}/instrument/{instrument}/price-observations/{priceObservation}', [InstrumentPriceObservationController::class, 'updateForInstrument'])
            ->name('instrument.price-observations.update');

        Route::delete('{knowledgeItem}/instrument/{instrument}/price-observations/{priceObservation}', [InstrumentPriceObservationController::class, 'destroyForInstrument'])
            ->name('instrument.price-observations.destroy');

        Route::post('{knowledgeItem}/instrument/{instrument}/corporate-actions', [InstrumentCorporateActionController::class, 'storeForInstrument'])
            ->name('instrument.corporate-actions.store');

        Route::put('{knowledgeItem}/instrument/{instrument}/corporate-actions/{corporateAction}', [InstrumentCorporateActionController::class, 'updateForInstrument'])
            ->name('instrument.corporate-actions.update');

        Route::delete('{knowledgeItem}/instrument/{instrument}/corporate-actions/{corporateAction}', [InstrumentCorporateActionController::class, 'destroyForInstrument'])
            ->name('instrument.corporate-actions.destroy');

        Route::post('{knowledgeItem}/instrument/{instrument}/transactions', [InstrumentTransactionController::class, 'storeForInstrument'])
            ->name('instrument.transactions.store');

        Route::put('{knowledgeItem}/instrument/{instrument}/transactions/{transaction}', [InstrumentTransactionController::class, 'updateForInstrument'])
            ->name('instrument.transactions.update');

        Route::delete('{knowledgeItem}/instrument/{instrument}/transactions/{transaction}', [InstrumentTransactionController::class, 'destroyForInstrument'])
            ->name('instrument.transactions.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Knowledge attachments and Bible references with standalone edit pages
|--------------------------------------------------------------------------
| These remain outside the nested item group because the edit routes are
| record-based rather than nested under the item URL.
*/
Route::prefix('knowledge-attachments')->name('knowledge.attachments.')->group(function () {
    Route::get('{knowledgeAttachment}/view', [KnowledgeAttachmentController::class, 'view'])->name('view');
    Route::get('{knowledgeAttachment}/download', [KnowledgeAttachmentController::class, 'download'])->name('download');
});

Route::prefix('knowledge-items/{knowledgeItem}/attachments')->name('knowledge.attachments.')->group(function () {
    Route::post('/', [KnowledgeAttachmentController::class, 'store'])->name('store');
    Route::post('attach-existing', [KnowledgeAttachmentController::class, 'attachExisting'])->name('attach-existing');
    Route::get('{knowledgeAttachment}/edit', [KnowledgeAttachmentController::class, 'edit'])->name('edit');
    Route::put('{knowledgeAttachment}', [KnowledgeAttachmentController::class, 'update'])->name('update');
    Route::delete('{knowledgeAttachment}', [KnowledgeAttachmentController::class, 'destroy'])->name('destroy');
    
});

Route::prefix('bible-references')->name('knowledge.items.bible-references.')->group(function () {
    Route::get('{bibleReference}/edit', [BibleReferenceController::class, 'edit'])->name('edit');
    Route::put('{bibleReference}', [BibleReferenceController::class, 'update'])->name('update');
    Route::delete('{bibleReference}', [BibleReferenceController::class, 'destroy'])->name('destroy');
    Route::post('{bibleReference}/fetch-passage', [BibleReferenceController::class, 'fetchPassage'])->name('fetch-passage');
});