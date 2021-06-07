<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Http\Resources\Test\TestResource;
use App\Http\Resources\Test\TestCollection;
use App\Http\Requests\Test\StoreTestRequest;
use App\Http\Requests\Test\UpdateTestRequest;
use App\Models\Test;

/**
 * @group Test
 *
 * Endpoints for Test entity
 */
class TestController extends Controller
{

    /**
     * Create a new TestController instance.
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');

        $this->middleware('verified');
    }

    /**
     * Index
     *
     * Get paginated list of items.
     * @param Request $request
     * @return JsonResponse
     * @authenticated
     * @apiResourceCollection App\Http\Resources\Test\TestResource
     * @apiResourceModel App\Models\Test paginate=10
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Test::class);

        $tests = Test::search()->paginate($request->perPage)
            ->appends(request()->query());

        return response()->resource(new TestCollection($tests));
    }

    /**
     * Store
     *
     * Store newly created test.
     * @param  StoreTestRequest  $request
     * @return JsonResponse
     * @authenticated
     * @apiResource App\Http\Resources\Test\TestResource
     * @apiResourceModel App\Models\Test
     */
    public function store(StoreTestRequest $request): JsonResponse
    {
        $this->authorize('create', Test::class);

        $test = $request->fill(new Test);

        $test->save();
        $test->loadIncludes();

        return response()->resource(new TestResource($test))
                    ->message(__('crud.create', ['item' => __('model.Test')]));
    }

    /**
     * Update
     *
     * Update specified test.
     * @param  UpdateTestRequest  $request
     * @param  Test $test
     * @return JsonResponse
     * @authenticated
     * @apiResource App\Http\Resources\Test\TestResource
     * @apiResourceModel App\Models\Test
     */
    public function update(UpdateTestRequest $request, Test $test): JsonResponse
    {
        $this->authorize('update', $test);

        $request->fill($test);
        
        $test->update();
        $test->loadIncludes();

        return response()->resource(new TestResource($test))
                    ->message(__('crud.update', ['item' => __('model.Test')]));
    }
    /**
     * Show
     *
     * Display specified test.
     * @param  Test $test
     * @return JsonResponse
     * @authenticated
     * @apiResource App\Http\Resources\Test\TestResource
     * @apiResourceModel App\Models\Test
     */
    public function show(Test $test): JsonResponse
    {
        $this->authorize('view', $test);

        $test->loadIncludes();

        return response()->resource(new TestResource($test));
    }

    /**
     * Destroy
     *
     * Remove specified test.

     * @param  Test  $test
     * @return  JsonResponse
     * @authenticated
     */
    public function destroy(Test $test): JsonResponse
    {
        $this->authorize('delete', $test);

        $test->delete();

        return response()
                    ->success(__('crud.delete', ['item' => __('model.Test')]));
    }
}
