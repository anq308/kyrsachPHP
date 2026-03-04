<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Favorite;
use App\Models\Motorcycle;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SpaApiController extends Controller
{
    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();
        $cart = session()->get('cart', []);
        $compare = session()->get('compare', []);

        return response()->json([
            'user' => $this->userPayload($user),
            'cart_count' => count($cart),
            'compare_count' => count($compare),
        ]);
    }

    public function home(): JsonResponse
    {
        $popularMotorcycles = Motorcycle::where('is_available', true)
            ->orderByDesc('views_count')
            ->take(3)
            ->get();

        return response()->json([
            'popularMotorcycles' => $popularMotorcycles,
            'news' => $this->newsItems(),
        ]);
    }

    public function catalog(Request $request): JsonResponse
    {
        $query = Motorcycle::where('is_available', true);

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = max(1, min(30, (int) $request->input('per_page', 9)));
        $motorcycles = $query->paginate($perPage)->withQueryString();

        return response()->json($motorcycles);
    }

    public function motorcycle(Request $request, string $id): JsonResponse
    {
        $motorcycle = Motorcycle::findOrFail($id);
        $motorcycle->increment('views_count');
        $motorcycle->refresh();

        $compare = session()->get('compare', []);
        $user = $request->user();

        return response()->json([
            'motorcycle' => $motorcycle,
            'is_in_compare' => in_array($motorcycle->id, $compare, true),
            'is_favorite' => $user ? $user->hasFavorite($motorcycle->id) : false,
        ]);
    }

    public function compareIndex(): JsonResponse
    {
        $compareIds = session()->get('compare', []);
        $motorcycles = Motorcycle::whereIn('id', $compareIds)->get();

        return response()->json([
            'motorcycles' => $motorcycles,
            'ids' => $compareIds,
        ]);
    }

    public function toggleCompare(string $id): JsonResponse
    {
        Motorcycle::findOrFail($id);
        $compare = session()->get('compare', []);
        $idInt = (int) $id;

        if (in_array($idInt, $compare, true)) {
            $compare = array_values(array_diff($compare, [$idInt]));
            session()->put('compare', $compare);

            return response()->json([
                'message' => 'Товар удалён из сравнения.',
                'ids' => $compare,
            ]);
        }

        if (count($compare) >= 4) {
            return response()->json([
                'message' => 'Можно сравнивать не более 4 товаров.',
            ], 422);
        }

        $compare[] = $idInt;
        session()->put('compare', $compare);

        return response()->json([
            'message' => 'Товар добавлен к сравнению!',
            'ids' => $compare,
        ]);
    }

    public function cartIndex(): JsonResponse
    {
        $cart = session()->get('cart', []);

        return response()->json($this->normalizeCart($cart));
    }

    public function cartAdd(Request $request, string $id): JsonResponse
    {
        $motorcycle = Motorcycle::findOrFail($id);
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity']++;
        } else {
            $cart[$id] = [
                'name' => $motorcycle->brand.' '.$motorcycle->model,
                'quantity' => 1,
                'price' => $motorcycle->price,
                'image' => $motorcycle->image_url,
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'message' => 'Товар добавлен в корзину!',
            'buy_now' => $request->boolean('buy_now'),
            'cart' => $this->normalizeCart($cart),
        ]);
    }

    public function cartRemove(string $id): JsonResponse
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return response()->json([
            'message' => 'Товар удален из корзины!',
            'cart' => $this->normalizeCart($cart),
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json(['message' => 'Корзина пуста!'], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'comment' => 'nullable|string|max:1000',
        ]);

        $total = 0;
        foreach ($cart as $item) {
            $total += ((int) $item['price']) * ((int) $item['quantity']);
        }

        $order = DB::transaction(function () use ($validated, $total, $cart) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'] ?? null,
                'comment' => $validated['comment'] ?? null,
                'total' => $total,
                'status' => 'new',
            ]);

            foreach ($cart as $id => $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'motorcycle_id' => is_numeric($id) ? (int) $id : null,
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return $order;
        });

        session()->forget('cart');

        return response()->json([
            'message' => 'Заказ #'.$order->id.' успешно оформлен! Мы свяжемся с вами в ближайшее время.',
            'order_id' => $order->id,
        ], 201);
    }

    public function contact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'message' => 'required|string|max:2000',
        ]);

        ContactMessage::create($validated);

        return response()->json([
            'message' => 'Сообщение отправлено! Мы свяжемся с вами в ближайшее время.',
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Неверные учетные данные.',
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Вход выполнен.',
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Регистрация прошла успешно!',
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Вы вышли из системы.',
        ]);
    }

    public function favoritesIndex(Request $request): JsonResponse
    {
        $favorites = $request->user()
            ->favorites()
            ->with('motorcycle')
            ->latest()
            ->get()
            ->pluck('motorcycle')
            ->filter()
            ->values();

        return response()->json([
            'favorites' => $favorites,
        ]);
    }

    public function toggleFavorite(Request $request, string $id): JsonResponse
    {
        $motorcycle = Motorcycle::findOrFail($id);
        $user = $request->user();

        $existing = Favorite::where('user_id', $user->id)
            ->where('motorcycle_id', $motorcycle->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json([
                'message' => 'Товар удалён из избранного.',
                'is_favorite' => false,
            ]);
        }

        Favorite::create([
            'user_id' => $user->id,
            'motorcycle_id' => $motorcycle->id,
        ]);

        return response()->json([
            'message' => 'Товар добавлен в избранное!',
            'is_favorite' => true,
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $orders = $user->orders()->with('items')->latest()->get();

        return response()->json([
            'user' => $this->userPayload($user),
            'orders' => $orders,
            'favorites_count' => $user->favorites()->count(),
        ]);
    }

    public function adminDashboard(): JsonResponse
    {
        $motorcycles = Motorcycle::latest()->get();
        $orders = Order::with(['items', 'user'])->latest()->get();
        $users = User::with('orders')->latest()->get();

        return response()->json([
            'motorcycles' => $motorcycles,
            'orders' => $orders,
            'users' => $users,
            'stats' => [
                'usersCount' => $users->count(),
                'ordersCount' => $orders->count(),
                'totalRevenue' => $orders->where('status', '!=', 'cancelled')->sum('total'),
                'unavailableCount' => $motorcycles->where('is_available', false)->count(),
            ],
        ]);
    }

    public function adminStoreMotorcycle(Request $request): JsonResponse
    {
        $validated = $request->validate($this->motorcycleRules());
        $validated['is_available'] = $request->boolean('is_available');

        $motorcycle = Motorcycle::create($validated);

        return response()->json([
            'message' => 'Товар успешно добавлен.',
            'motorcycle' => $motorcycle,
        ], 201);
    }

    public function adminUpdateMotorcycle(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate($this->motorcycleRules());
        $validated['is_available'] = $request->boolean('is_available');

        $motorcycle = Motorcycle::findOrFail($id);
        $motorcycle->update($validated);

        return response()->json([
            'message' => 'Товар успешно обновлён.',
            'motorcycle' => $motorcycle,
        ]);
    }

    public function adminDeleteMotorcycle(string $id): JsonResponse
    {
        $motorcycle = Motorcycle::findOrFail($id);
        $motorcycle->delete();

        return response()->json([
            'message' => 'Товар удалён.',
        ]);
    }

    public function adminUpdateOrderStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:new,processing,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->input('status')]);

        return response()->json([
            'message' => 'Статус заказа #'.$order->id.' обновлён.',
            'order' => $order,
        ]);
    }

    private function userPayload(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => (bool) $user->is_admin,
            'created_at' => optional($user->created_at)?->toISOString(),
        ];
    }

    private function normalizeCart(array $cart): array
    {
        $items = [];
        $total = 0;

        foreach ($cart as $id => $item) {
            $quantity = (int) $item['quantity'];
            $price = (int) $item['price'];
            $lineTotal = $quantity * $price;
            $total += $lineTotal;

            $items[] = [
                'id' => is_numeric($id) ? (int) $id : $id,
                'name' => $item['name'],
                'quantity' => $quantity,
                'price' => $price,
                'image' => $item['image'],
                'line_total' => $lineTotal,
            ];
        }

        return [
            'items' => $items,
            'total' => $total,
            'count' => count($items),
        ];
    }

    private function motorcycleRules(): array
    {
        return [
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:'.(date('Y') + 1),
            'engine_capacity' => 'required|integer',
            'power' => 'required|integer',
            'price' => 'required|integer',
            'description' => 'required|string',
            'image_url' => [
                'required',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (filter_var($value, FILTER_VALIDATE_URL) || str_starts_with($value, '/')) {
                        return;
                    }

                    $fail('Поле изображения должно быть абсолютным URL или начинаться с "/".');
                },
            ],
            'is_available' => 'boolean',
            'transmission' => 'nullable|string|max:255',
            'cooling' => 'nullable|string|max:255',
            'fuel_system' => 'nullable|string|max:255',
            'weight' => 'nullable|integer',
            'tank_capacity' => 'nullable|numeric',
        ];
    }

    private function newsItems(): array
    {
        return [
            [
                'title' => 'Открытие нового сезона эндуро 2026',
                'date' => '12 Мая 2026',
                'image' => asset('images/news_season.png'),
                'excerpt' => 'Готовим технику и экипировку. Расписание гонок и тренировок на ближайший месяц.',
            ],
            [
                'title' => 'Поступление новой коллекции Avantis',
                'date' => '28 Апреля 2026',
                'image' => asset('images/news_collection.png'),
                'excerpt' => 'Обновленные двигатели, улучшенная подвеска и агрессивный дизайн. Успей забронировать.',
            ],
            [
                'title' => 'Как подготовить байк к сезону?',
                'date' => '15 Апреля 2026',
                'image' => asset('images/news_maintenance.png'),
                'excerpt' => 'Советы от наших механиков: проверка жидкостей, цепи и резины перед первым выездом.',
            ],
        ];
    }
}
