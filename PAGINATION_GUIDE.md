# Custom Pagination for Laravel Livewire

## Overview

Custom pagination views dengan Tailwind CSS yang modern dan user-friendly untuk semua halaman admin.

## Features

### ✨ Full Pagination View (`tailwind.blade.php`)

**Features:**
- 🔢 **Page Numbers** - Tampilkan nomor halaman dengan indicator active
- 📊 **Data Info** - "Menampilkan 1 sampai 10 dari 100 data"
- ⏮️ **Previous/Next Buttons** - Navigasi dengan icon
- 🎨 **Modern Design** - Gradient active state, rounded corners, smooth transitions
- 📱 **Responsive** - Mobile-friendly dengan fallback untuk layar kecil
- ♿ **Accessible** - Proper ARIA labels dan keyboard navigation

**Preview:**
```
Menampilkan 1 sampai 10 dari 100 data     [<] [1] [2] [3] ... [10] [>]
```

### 🚀 Simple Pagination View (`simple-tailwind.blade.php`)

**Features:**
- ⏮️ **Previous/Next Only** - Untuk dataset besar atau infinite scroll
- 📄 **Current Page** - "Halaman 5"
- 🎨 **Minimalist** - Clean design tanpa clutter
- 📱 **Responsive** - Works on all screen sizes

**Preview:**
```
[< Previous]     Halaman 5     [Next >]
```

---

## Automatic Usage

Karena config Livewire sudah set ke `'pagination_theme' => 'tailwind'`, maka **semua pagination akan otomatis menggunakan custom view**.

### Components yang Sudah Support:

✅ **Users Management** (`/admin/users`)
✅ **Students Management** (`/admin/students`)
✅ **Classes Management** (`/admin/classes`)
✅ **Departments Management** (`/admin/departments`)
✅ **Attendance Data** (`/admin/attendance`)
✅ **Academic Calendar** (`/admin/calendar`)
✅ **Activity Logs** (`/admin/system/logs`)
✅ **Semester Settings** (`/admin/settings/semesters`)

---

## Design Specifications

### Color Scheme

**Active Page:**
- Background: `bg-gradient-to-r from-blue-600 to-blue-700`
- Text: `text-white`
- Border: `border-blue-600`
- Shadow: `shadow-sm`

**Inactive Pages:**
- Background: `bg-white`
- Text: `text-slate-700`
- Border: `border-slate-200`
- Hover: `bg-slate-50`, `border-slate-300`

**Disabled State:**
- Text: `text-slate-400`
- Cursor: `cursor-default`

### Spacing & Layout

- Button padding: `px-4 py-2` (standard), `px-3 py-2` (prev/next)
- Gap between elements: `gap-1`
- Border radius: `rounded-lg`
- Container padding: `px-6 py-4`

### Typography

- Font size: `text-sm`
- Font weight:
  - Active: `font-semibold`
  - Others: `font-medium`

### Transitions

All interactive elements have smooth transitions:
```css
transition ease-in-out duration-150
```

---

## Usage in Livewire Components

### Method 1: Default Pagination (Recommended)

```php
use Livewire\WithPagination;

class StudentIndex extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.admin.students.student-index', [
            'students' => Student::paginate(10)
        ]);
    }
}
```

**Blade View:**
```html
<div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
    {{ $students->links() }}
</div>
```

### Method 2: Simple Pagination

For very large datasets:

```php
public function render()
{
    return view('livewire.admin.students.student-index', [
        'students' => Student::simplePaginate(10)
    ]);
}
```

**Blade View:**
```html
<div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
    {{ $students->links('vendor.livewire.simple-tailwind') }}
</div>
```

### Method 3: Custom Per Page

```php
public $perPage = 25;

public function render()
{
    return view('livewire.admin.students.student-index', [
        'students' => Student::paginate($this->perPage)
    ]);
}
```

---

## Responsive Behavior

### Desktop (≥640px)
```
[Info Text]                    [<] [1] [2] [3] ... [10] [>]
```

### Mobile (<640px)
```
[< Previous]            [Next >]
```

Page numbers are hidden on mobile untuk space efficiency.

---

## Browser Support

✅ Chrome/Edge (latest)
✅ Firefox (latest)
✅ Safari (latest)
✅ Mobile browsers (iOS Safari, Chrome Mobile)

---

## Customization

### Change Colors

Edit `resources/views/vendor/livewire/tailwind.blade.php`:

```html
<!-- Active page (line ~130) -->
<span class="... bg-gradient-to-r from-purple-600 to-purple-700 ...">

<!-- Inactive page hover (line ~135) -->
<button class="... hover:bg-purple-50 hover:border-purple-300 ...">
```

### Change Items Per Page

In your Livewire component:

```php
protected $paginationTheme = 'tailwind'; // Default

public $perPage = 15; // Change from 10 to 15

public function render()
{
    return view('...', [
        'items' => Model::paginate($this->perPage)
    ]);
}
```

### Add Per-Page Selector

```html
<div class="flex items-center gap-4">
    <select wire:model.live="perPage" class="rounded-lg border-slate-200">
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
        <option value="100">100</option>
    </select>
    <span class="text-sm text-slate-600">per halaman</span>
</div>
```

---

## Performance Optimization

### For Large Datasets

Use `simplePaginate()` instead of `paginate()`:

```php
// Instead of:
Student::paginate(10); // Counts total records

// Use:
Student::simplePaginate(10); // No count query, faster
```

**Trade-off:** No page numbers, only prev/next buttons.

### Eager Loading

Prevent N+1 queries:

```php
Student::with(['class', 'user'])->paginate(10);
```

### Caching

For rarely-changed data:

```php
$students = Cache::remember('students-page-' . request('page', 1), 300, function() {
    return Student::paginate(10);
});
```

---

## Troubleshooting

### Pagination Not Updating

**Problem:** Pagination keeps showing old style

**Solution:**
```bash
# Clear view cache
php artisan view:clear

# Clear config cache
php artisan config:clear

# Hard refresh browser
Ctrl + Shift + R (Windows/Linux)
Cmd + Shift + R (Mac)
```

### Custom View Not Found

**Problem:** `View [vendor.livewire.tailwind] not found`

**Solution:**
1. Ensure files exist:
   - `resources/views/vendor/livewire/tailwind.blade.php`
   - `resources/views/vendor/livewire/simple-tailwind.blade.php`

2. Check config:
   ```php
   // config/livewire.php
   'pagination_theme' => 'tailwind', // Not 'bootstrap'
   ```

### Styling Issues

**Problem:** Pagination looks broken or unstyled

**Solution:**
1. Ensure Tailwind CSS is loaded in layout
2. Check if custom CSS is overriding Tailwind classes
3. Rebuild Tailwind:
   ```bash
   npm run build
   ```

### Wire:loading Not Working

**Problem:** Loading indicator not showing during pagination

**Add to pagination buttons:**
```html
<button wire:click="..." wire:loading.attr="disabled" wire:loading.class="opacity-50">
    <span wire:loading.remove>Next</span>
    <span wire:loading>Loading...</span>
</button>
```

---

## File Locations

```
resources/views/vendor/livewire/
├── tailwind.blade.php         # Full pagination with page numbers
└── simple-tailwind.blade.php  # Simple prev/next pagination

config/
└── livewire.php               # Set 'pagination_theme' => 'tailwind'
```

---

## Example Implementation

### Complete Example: Student Index

**Component (`app/Livewire/Admin/Students/StudentIndex.php`):**
```php
<?php

namespace App\Livewire\Admin\Students;

use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class StudentIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    public function updatingSearch()
    {
        $this->resetPage(); // Reset to page 1 on search
    }

    public function render()
    {
        $students = Student::query()
            ->when($this->search, function($query) {
                $query->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('nis', 'like', '%' . $this->search . '%');
            })
            ->with(['class', 'user'])
            ->orderBy('full_name')
            ->paginate($this->perPage);

        return view('livewire.admin.students.student-index', [
            'students' => $students
        ]);
    }
}
```

**View (`resources/views/livewire/admin/students/student-index.blade.php`):**
```html
<div>
    <!-- Search & Filters -->
    <div class="mb-6">
        <input wire:model.live.debounce.300ms="search"
               type="text"
               placeholder="Cari siswa..."
               class="rounded-lg border-slate-200">
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <!-- Table content -->
        </table>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $students->links() }}
        </div>
    </div>
</div>
```

---

## Best Practices

### ✅ DO

1. **Use consistent container styling:**
   ```html
   <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
       {{ $items->links() }}
   </div>
   ```

2. **Reset page on search/filter:**
   ```php
   public function updatingSearch()
   {
       $this->resetPage();
   }
   ```

3. **Use eager loading:**
   ```php
   Model::with('relations')->paginate(10);
   ```

4. **Add loading states:**
   ```html
   <div wire:loading class="opacity-50">Loading...</div>
   ```

### ❌ DON'T

1. **Don't forget to use WithPagination trait**
   ```php
   // ❌ Missing trait
   class MyComponent extends Component { }

   // ✅ Correct
   class MyComponent extends Component {
       use WithPagination;
   }
   ```

2. **Don't paginate without ordering:**
   ```php
   // ❌ Unpredictable results
   Model::paginate(10);

   // ✅ Consistent results
   Model::orderBy('created_at', 'desc')->paginate(10);
   ```

3. **Don't hard-code pagination in loops:**
   ```php
   // ❌ Wrong
   @foreach(Model::paginate(10) as $item)

   // ✅ Correct
   // In controller/component:
   $items = Model::paginate(10);
   // In view:
   @foreach($items as $item)
   ```

---

## Testing

### Manual Testing Checklist

- [ ] Page 1 shows first items
- [ ] Click page 2 loads next items
- [ ] Previous button disabled on page 1
- [ ] Next button disabled on last page
- [ ] Active page highlighted correctly
- [ ] Info text shows correct counts
- [ ] Mobile view works (prev/next only)
- [ ] Loading state shows during transition
- [ ] Keyboard navigation works (Tab, Enter)

### Automated Testing

```php
/** @test */
public function it_paginates_students()
{
    Student::factory()->count(25)->create();

    Livewire::test(StudentIndex::class)
        ->assertSee('Menampilkan 1 sampai 10 dari 25 data')
        ->call('nextPage')
        ->assertSee('Menampilkan 11 sampai 20 dari 25 data');
}
```

---

**Last Updated:** 2026-01-05
**Version:** 1.0.0
**Maintainer:** SMK Negeri 10 Pandeglang
