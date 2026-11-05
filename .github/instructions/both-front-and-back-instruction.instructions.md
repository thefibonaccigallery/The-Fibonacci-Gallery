---
description: These instructions are the single source of truth for this project. All AI agents working on the frontend or backend must follow every rule listed below strictly, without deviation, assumption, or personal interpretation. No rule may be skipped, modified, or overridden unless explicitly updated in this file.
applyTo: "**"
---

Provide project context and coding guidelines that AI should follow when generating code, answering questions, or reviewing changes.These instructions are the single source of truth for this project. All AI agents working on the frontend or backend must follow every rule listed below strictly, without deviation, assumption, or personal interpretation. No rule may be skipped, modified, or overridden unless explicitly updated in this file.

## 1. Always Use @ Alias for Imports — Never Relative Paths

- NEVER use relative paths (./ or ../) for importing files.
- ALWAYS use the @ alias which points to the src directory.

```ts
// ❌ Bad — relative imports
import UserHeader from "./UserHeader.vue";
import { useAuth } from "../../composables/useAuth";
import MyComponent from "../components/MyComponent.vue";

// ✅ Good — @ alias imports
import UserHeader from "@/components/UserHeader.vue";
import { useAuth } from "@/composables/useAuth";
import MyComponent from "@/components/MyComponent.vue";
```

- This applies to ALL imports including:
  - Vue components
  - Composables
  - Stores
  - Utils and helpers
  - Assets (images, icons, styles)
  - Types and interfaces
  - Constants and configs

---

## 2. Always Use Composables for Logic — Vue Files for UI Only

- NEVER write business logic directly in Vue component files.
- ALWAYS extract logic into composables.
- Vue files should only contain template and minimal UI-related code.
- The composable handles: data fetching, form state, errors, mutations.
- The Vue file only consumes what the composable returns.

```ts
// ❌ Bad — logic in Vue file
<script setup>
const users = ref([]);
const fetchUsers = async () => {
    const response = await axios.get('/api/users');
    users.value = response.data;
};
</script>

// ✅ Good — logic in composable
// composables/useUsers.ts
export const useUsers = () => {
    const users = ref([]);
    const fetchUsers = async () => { ... };
    return { users, fetchUsers };
};

// UserList.vue
<script setup>
const { users, fetchUsers } = useUsers();
</script>
```

---

## 3. Always Write TypeScript Types in Separate Files

- NEVER define TypeScript types or interfaces inline in Vue or composable files.
- ALWAYS create a dedicated type file for each feature/module.
- Type files must be named descriptively e.g. locationType.ts, userType.ts

```ts
// BEFORE CREATING ANY TYPE:
// 1. ALWAYS check @/types/CommonTypes first for shared/reusable types
// 2. ALWAYS check if the type already exists in other type files
// 3. ALWAYS import and reuse existing types instead of redefining them
// 4. Only create a new type if it genuinely does not exist anywhere
```

```ts
// ❌ Bad — redefining types that already exist elsewhere
export interface ProductGroupType {
    product: {
        id: number;
        name: string;
        brand: string;
    };
    group_name: string;
}

// ❌ Bad — type defined inline in component
<script setup lang="ts">
interface User {
    id: number;
    name: string;
}
</script>

// ✅ Good — reuse existing types, import from other type files
import type { ProductType } from '@/types/productType';

export interface ProductGroupType {
    product: ProductType;
    group_name: string;
}
```

```ts
// TYPE LOOKUP ORDER — always follow this before creating any type:
// 1. Check @/types/CommonTypes → shared primitive/common types
// 2. Check @/types/{feature}Type.ts → feature-specific types
// 3. Check other related type files → related model types
// 4. Only then create a new type if it truly does not exist

// Rules:
// - NEVER redefine a type that already exists — always import it
// - NEVER nest raw object shapes when a named type exists for it
// - ALWAYS import types using 'import type' not 'import'

// ✅ Good
import type { ProductType } from "@/types/productType";
import type { CommonIdName } from "@/types/CommonTypes";

// ❌ Bad
import { ProductType } from "@/types/productType";
```

---

## 4. Always Use Zod for Form Validation in Separate Schema Files

- NEVER write inline validation logic in components or composables.
- ALWAYS use Zod for form validation.
- ALWAYS create a dedicated schema file for each form.
- Schema files must be named descriptively e.g. loginSchemas.ts, userSchemas.ts
- ALWAYS export both FormState and FormField types from every schema file.

```ts
// ❌ Bad — inline validation
if (!email.value) {
	errors.value.email = "Email is required";
}

// ✅ Good — Zod schema in separate file
// schemas/loginSchemas.ts
import { z } from "zod";

export const loginSchema = z.object({
	email: z.string().email("Invalid email address"),
	password: z.string().min(8, "Password must be at least 8 characters"),
});

export type LoginFormState = z.infer<typeof loginSchema>;
export type LoginFormField = keyof LoginFormState;
```

---

## 5. Always Use Standardized Error Handling Pattern in Composables

- NEVER handle form errors manually or inconsistently.
- ALWAYS follow the standardized error handling pattern using validateFormWithZod and NormalizedErrors.

```ts
// STEP 1 — Composable maintains a reactive errors object
import {
	validateFormWithZod,
	normalizeErrors,
	type NormalizedErrors,
} from "@/utils/errorFormatter";
import type { LoginFormField } from "@/schemas/loginSchemas";

const errors = ref<NormalizedErrors<LoginFormField>>({});
```

```ts
// STEP 2 — clearError removes a single field error when user edits that field
function clearError(field: LoginFormField) {
	if (errors.value && errors.value[field]) {
		delete errors.value[field];
	}
}
// Rules:
// - ALWAYS export clearError from the composable
// - ALWAYS call clearError when the user starts editing a field
// - NEVER clear all errors at once on field change
```

```ts
// STEP 3 — Mutations: normalize field errors or toast message
} catch (err: any) {
    if (err.response?.data?.errors) {
        errors.value = normalizeErrors<LoginFormField>(err.response.data.errors);
    } else {
        toast.error(err.response?.data?.message);
    }
}
```

```ts
// STEP 4 — Queries: toast message then rethrow
} catch (err: any) {
    toast.error(err.response?.data?.message);
    throw err;
}
```

```ts
// STEP 5 — Form submit: clear errors, validate with Zod, then mutate
async function handleSubmit() {
	errors.value = {};

	const error = validateFormWithZod<LoginFormField>(loginSchema, form.value);
	if (error) {
		errors.value = error;
		return;
	}

	loginMutation.mutate(form.value);
}
// Rules:
// - ALWAYS reset errors to {} at the start
// - ALWAYS validate BEFORE the API call
// - ALWAYS return early if validation fails
```

```vue
// STEP 6 — Form component: bind :invalid, show error text, call clearError
<template>
	<input
		v-model="form.email"
		:invalid="!!errors.email"
		@input="clearError('email')"
	/>
	<small v-if="errors.email">{{ errors.email }}</small>

	<input
		v-model="form.password"
		type="password"
		:invalid="!!errors.password"
		@input="clearError('password')"
	/>
	<small v-if="errors.password">{{ errors.password }}</small>

	<button @click="handleSubmit">Login</button>
</template>
```

---

## 6. Never Hardcode Colors — Always Use the Color Palette

- NEVER use hardcoded color values (hex, rgb, hsl) anywhere in the codebase.
- ALWAYS use Tailwind CSS color utility classes.
- If a color is not available in the default Tailwind palette, add it to the theme using the Tailwind 4 `@theme` format.

```css
/* ✅ Good — adding a custom color in Tailwind 4 */
@theme {
	--color-mint-500: oklch(0.72 0.11 178);
	--color-brand-primary: oklch(0.55 0.18 250);
}
```

```vue
<!-- ❌ Bad — hardcoded color -->
<div style="color: #3490dc">Hello</div>

<!-- ✅ Good — Tailwind utility class -->
<div class="text-mint-500">Hello</div>
```

---

## 7. Always Use TanStack Query for Data Fetching and State Management

- NEVER use raw axios calls or manual ref/reactive state for server data.
- ALWAYS use TanStack Query (useQuery, useMutation) for all data fetching and server state.

```ts
// ❌ Bad — manual fetch in composable
const users = ref([]);
const fetchUsers = async () => {
	const res = await axios.get("/api/users");
	users.value = res.data;
};

// ✅ Good — TanStack Query
import { useQuery, useMutation } from "@tanstack/vue-query";

const { data: users, isLoading } = useQuery({
	queryKey: ["users"],
	queryFn: () => axios.get("/api/users").then((r) => r.data),
});

const { mutate: createUser } = useMutation({
	mutationFn: (payload) => axios.post("/api/users", payload),
	onSuccess: () => queryClient.invalidateQueries({ queryKey: ["users"] }),
});
```

---

## 8. Always Use TanStack useForm to Handle Forms

- NEVER use manual ref objects to track form state.
- ALWAYS use TanStack useForm for all form handling.
- Combine with Zod schema from the dedicated schema file for validation.

```ts
// ❌ Bad — manual form state
const form = ref({ email: "", password: "" });

// ✅ Good — TanStack useForm with Zod
import { useForm } from "@tanstack/vue-form";
import { loginSchema } from "@/schemas/loginSchemas";

const form = useForm({
	defaultValues: { email: "", password: "" },
	validatorAdapter: zodValidator(loginSchema),
});
```

---

## 9. Always Use Shadcn Vue Components

- NEVER build UI components from scratch if a Shadcn Vue equivalent exists.
- ALWAYS use Shadcn Vue for all UI elements — buttons, inputs, dialogs, cards, etc.
- Extend or wrap Shadcn components when customization is needed rather than building new ones.

```vue
<!-- ❌ Bad — custom button -->
<button class="bg-blue-500 text-white px-4 py-2 rounded">Submit</button>

<!-- ✅ Good — Shadcn Button -->
<Button variant="default">Submit</Button>
```

---

## 10. Always Design Mobile First

- NEVER design for desktop first and adapt down.
- ALWAYS start with mobile layout and scale up using Tailwind responsive prefixes (sm:, md:, lg:).
- The primary user device is a mobile phone — every component must look and work correctly on small screens first.

```vue
<!-- ❌ Bad — desktop first -->
<div class="grid grid-cols-4 md:grid-cols-1">...</div>

<!-- ✅ Good — mobile first -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4">...</div>
```

---

## 11. Never Hardcode Text — Always Use the Language Store

- NEVER hardcode any visible text strings in templates or scripts.
- ALWAYS use the language system for all user-facing text.

```vue
<!-- ❌ Bad — hardcoded text in template -->
<p>Welcome to the login page</p>

<!-- ✅ Good — language key in template -->
<p>{{ lang.login.welcome }}</p>
```

```ts
// ❌ Bad — hardcoded text in script
console.log("Hello");

// ✅ Good — language store in script or .ts file
import { useLanguageStore } from "@/stores/languageStore";
const languageStore = useLanguageStore();
console.log(languageStore.translations.login.hello);
```

- In `.vue` templates use `lang.login.keyName`
- In `script` blocks and `.ts` files use `languageStore.translations.login.keyName`
- NEVER use raw strings for labels, placeholders, error messages, or button text

---

Here is the new rule:

---

## 12. Never Use Native HTML Validation — Always Use Zod

- NEVER use native HTML validation attributes on any input element.
- ALWAYS rely on Zod schema validation defined in the dedicated schema file.
- Remove all native validation attributes from inputs — these include but are not limited to:

```vue
<!-- ❌ Bad — native HTML validation -->
<input required />
<input type="email" />
<input minlength="8" />
<input maxlength="255" />
<input pattern="[a-z]+" />
<input min="0" max="100" />
```

```vue
<!-- ✅ Good — plain input, Zod handles all validation -->
<input
	v-model="form.email"
	@input="clearError('email')"
	:invalid="!!errors.email"
/>
<small v-if="errors.email">{{ errors.email }}</small>
```

- This applies to ALL form inputs including Shadcn Vue input components.
- The only exception is `type` attribute when it is needed for UI purposes only (e.g. `type="password"` to mask input, `type="number"` to show numeric keyboard on mobile) — but never for validation.

---

## 12. Never Create a Summary Document

- NEVER create summary documents, readme files, explanation files, or any markdown documentation files unless explicitly asked.
- ALWAYS focus only on the code and files required for the task.

---


##  13. Never Use Window Alert Dialogs — Always Use Shadcn Vue Dialog

- NEVER use `window.alert()`, `window.confirm()`, or `window.prompt()` anywhere in the codebase.
- ALWAYS use the Shadcn Vue `Dialog` or `AlertDialog` component instead.

```ts
// ❌ Bad
window.alert('Course deleted successfully')
window.confirm('Are you sure you want to delete this course?')
```

```vue
<!-- ✅ Good — use Shadcn AlertDialog for confirmations -->
<AlertDialog>
  <AlertDialogTrigger as-child>
    <Button variant="destructive">Delete Course</Button>
  </AlertDialogTrigger>
  <AlertDialogContent>
    <AlertDialogHeader>
      <AlertDialogTitle>Are you sure?</AlertDialogTitle>
      <AlertDialogDescription>This action cannot be undone.</AlertDialogDescription>
    </AlertDialogHeader>
    <AlertDialogFooter>
      <AlertDialogCancel>Cancel</AlertDialogCancel>
      <AlertDialogAction @click="deleteCourse">Delete</AlertDialogAction>
    </AlertDialogFooter>
  </AlertDialogContent>
</AlertDialog>
```
Here is the new standalone rule:

---

## 15. Never Create New Migration Files During Development — Always Update Existing Ones

- NEVER create a new migration file to add, modify, rename, or remove columns during development.
- ALWAYS go back to the existing migration file and update it directly.
- After any migration change run `php artisan migrate:fresh` to rebuild the database.
- This applies to ALL schema changes including:
  - Adding new columns
  - Changing column types
  - Renaming columns
  - Adding or removing indexes
  - Adding or removing foreign keys

```php
// ❌ Bad — creating a new migration for a column change
php artisan make:migration add_watch_percentage_to_video_progress_table

// ✅ Good — open the existing migration file and update it directly
// database/migrations/xxxx_xx_xx_create_video_progress_table.php
Schema::create('video_progress', function (Blueprint $table) {
    $table->id();
    $table->uuid()->unique()->default(DB::raw('gen_random_uuid()'));
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('course_video_id')->constrained()->cascadeOnDelete();
    $table->boolean('is_completed')->default(false);
    $table->timestamp('completed_at')->nullable();
    $table->integer('watch_time_seconds')->default(0);
    $table->decimal('watch_percentage', 5, 2)->default(0.00); // ← added here directly
    $table->timestamp('last_watched_at')->nullable();
    $table->unique(['user_id', 'course_video_id']);
    $table->timestamps();
});

// Then run:
php artisan migrate:fresh
```

- This rule is only for development. Once the project is in production, always create new migration files instead.

