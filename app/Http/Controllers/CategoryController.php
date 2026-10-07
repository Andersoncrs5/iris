<?php

namespace App\Http\Controllers;

use App\DTOs\Category\CreateCategoryDTO;
use App\DTOs\Category\FilterCategoryDTO;
use App\DTOs\Category\UpdateCategoryDTO;
use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\CategoryModel;
use App\Services\Category\CreateCategoryUseCase;
use App\Services\Category\DeleteCategoryByIdUseCase;
use App\Services\Category\ListCategoriesUseCase;
use App\Services\Category\ToggleCategoryUseCase;
use App\Services\Category\UpdateCategoryUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController
{
    public function __construct(
        private readonly CreateCategoryUseCase $createCategoryUseCase,
        private readonly ListCategoriesUseCase $listCategoriesUseCase,
        private readonly ToggleCategoryUseCase $toggleCategoryUseCase,
        private readonly DeleteCategoryByIdUseCase $deleteCategoryByIdUseCase,
        private readonly UpdateCategoryUseCase $updateCategoryUseCase
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('category.list');

        $filterDto = FilterCategoryDTO::fromRequest($request);
        $categories = $this->listCategoriesUseCase->execute($filterDto);

        $parentCategories = CategoryModel::query()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('category.index', [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function create() 
    {
        Gate::authorize('category.create');

        $parentCategories = CategoryModel::query()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('category.create', [
            'parentCategories' => $parentCategories,
        ]);
    }

    public function store(CreateCategoryRequest $request) 
    {
        Gate::authorize('category.create');

        $dto = CreateCategoryDTO::fromRequest($request);
        $this->createCategoryUseCase->execute($dto);

        return redirect()
            ->route('category.list')
            ->with('success', 'Category created with success!');
    }

    public function edit(int $id) 
    {
        Gate::authorize('category.update');

        $category = CategoryModel::findOrFail($id);

        if ($category == null) {
            return redirect()->back()->with('warn', 'Category not found!');
        }

        return view('category.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, int $id) 
    {
        Gate::authorize('category.update');

        $dto = UpdateCategoryDTO::fromRequest($request, $id);

        $result = $this->updateCategoryUseCase->execute($dto, $id);

        if ($result->isFailure()) {
            return redirect()->back()->with('error', $result->getError());
        }

        return redirect()->route('category.list')->with('success', 'Category updated!');
    }

    public function toggleActive(int $id) 
    {
        Gate::authorize('category.update');

        $result = $this->toggleCategoryUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('category.list')
                ->with('error', $result->getError());
        }

        $category = $result->getValue();

        return redirect()
            ->route('category.list')
            ->with('success', "Category '{$category->name}' updated!");
    }

    public function destroy(int $id) 
    {
        Gate::authorize('category.delete');

        $result = $this->deleteCategoryByIdUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('category.list')
                ->with('error', $result->getError());
        }

        return redirect()->route('category.list')->with('success', "Category deleted!");
    }
}