<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="app-shell children-shell">
        <header class="children-header">
            <div class="children-header__inner">
                <div class="children-header__brand-group">
                    <Link :href="route('children.index')" class="children-header__brand">
                        <span class="app-header__mark">
                            <span class="app-header__mark-icon">
                                <span class="app-header__mark-bar app-header__mark-bar--h"></span>
                                <span class="app-header__mark-bar app-header__mark-bar--v"></span>
                            </span>
                        </span>L
                        <span class="children-header__brand-name">Módulo de Niños</span>
                    </Link>

                    <nav class="children-header__nav">
                        <Link
                            :href="route('children.index')"
                            class="children-header__nav-link"
                            :class="{ 'children-header__nav-link--active': route().current('children.index') }"
                        >
                            Listado
                        </Link>
                        <Link
                            :href="route('children.create')"
                            class="children-header__nav-link"
                            :class="{ 'children-header__nav-link--active': route().current('children.create') }"
                        >
                            Nuevo niño
                        </Link>
                    </nav>
                </div>

                <div class="children-header__actions">
                    <Link :href="route('dashboard')" class="children-header__exit">
                        <span class="children-header__exit-icon">←</span>
                        <span class="children-header__exit-label">Volver a Control de Donaciones</span>
                    </Link>

                    <Dropdown align="right">
                        <template #trigger>
                            <button type="button" class="children-header__user-trigger">
                                <span class="children-header__avatar">
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </span>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')">
                                Perfil
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Cerrar sesión
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </header>

        <header v-if="$slots.header" class="page-header">
            <div class="page-header__inner">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>

<style scoped>
/* Paleta deliberadamente distinta a la del sistema de donaciones (verde
   $color-primary): un morado/índigo propio para que quede claro a simple
   vista que este es un módulo aparte, no una sección más de Donaciones. */
.children-header {
    border-bottom: 1px solid #e2defa;
    background-color: #2c2560;
}

.children-header__inner {
    margin-inline: auto;
    display: flex;
    max-width: 72rem;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.875rem 1.25rem;
}

.children-header__brand-group {
    display: flex;
    align-items: center;
    gap: 1.75rem;
    min-width: 0;
}

.children-header__brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #fff;
    text-decoration: none;
}

.children-header__mark {
    font-size: 1.25rem;
    line-height: 1;
}

.children-header__brand-name {
    font-weight: 700;
    white-space: nowrap;
}

.children-header__nav {
    display: none;
    align-items: center;
    gap: 1.25rem;
}

.children-header__nav-link {
    font-size: 0.875rem;
    font-weight: 600;
    color: #cbc4f0;
    text-decoration: none;
}

.children-header__nav-link:hover {
    color: #fff;
}

.children-header__nav-link--active {
    color: #fff;
}

.children-header__actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Visible siempre (no solo en desktop): es la única salida del módulo en
   mobile, ya que acá no hay bottom-nav como en AuthenticatedLayout. En
   mobile se muestra solo el ícono para no competir por espacio con la
   marca y el avatar; el texto completo aparece desde tablet en adelante. */
.children-header__exit {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #cbc4f0;
    text-decoration: none;
    white-space: nowrap;
}

.children-header__exit:hover {
    color: #fff;
}

.children-header__exit-icon {
    font-size: 1rem;
    line-height: 1;
}

.children-header__exit-label {
    display: none;
}

.children-header__user-trigger {
    display: flex;
    align-items: center;
    border: none;
    background: none;
    padding: 0;
    cursor: pointer;
}

.children-header__avatar {
    display: flex;
    height: 2rem;
    width: 2rem;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background-color: #4a3f96;
    font-size: 0.8125rem;
    font-weight: 700;
    color: #fff;
}

@media (min-width: 768px) {
    .children-header__nav {
        display: flex;
    }

    .children-header__exit-label {
        display: inline;
    }
}
</style>
