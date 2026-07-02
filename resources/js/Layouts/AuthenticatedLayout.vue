<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { useInstallPrompt } from '@/composables/useInstallPrompt';
import { Link } from '@inertiajs/vue3';

const isAdmin = (user) => user.rol === 'admin';

const { canInstall, promptInstall, showIosInstallHint } = useInstallPrompt();
</script>

<template>
    <div class="app-shell">
        <header class="app-header">
            <div class="app-header__inner">
                <div class="app-header__brand-group">
                    <Link :href="route('dashboard')" class="app-header__brand">
                        <span class="app-header__mark">
                            <span class="app-header__mark-icon">
                                <span class="app-header__mark-bar app-header__mark-bar--h"></span>
                                <span class="app-header__mark-bar app-header__mark-bar--v"></span>
                            </span>
                        </span>
                        <span class="app-header__brand-name">
                            {{ $page.props.app?.name ?? 'Control de Donaciones' }}
                        </span>
                    </Link>

                    <nav class="app-header__nav">
                        <Link
                            :href="route('dashboard')"
                            class="app-header__nav-link"
                            :class="{ 'app-header__nav-link--active': route().current('dashboard') }"
                        >
                            Panel
                        </Link>
                        <Link
                            :href="route('donations.index')"
                            class="app-header__nav-link"
                            :class="{ 'app-header__nav-link--active': route().current('donations.*') }"
                        >
                            Donaciones
                        </Link>
                        <Link
                            v-if="isAdmin($page.props.auth.user)"
                            :href="route('admin.users.index')"
                            class="app-header__nav-link"
                            :class="{ 'app-header__nav-link--active': route().current('admin.users.*') }"
                        >
                            Usuarios
                        </Link>
                        <Link
                            v-if="isAdmin($page.props.auth.user)"
                            :href="route('admin.stock-items.index')"
                            class="app-header__nav-link"
                            :class="{ 'app-header__nav-link--active': route().current('admin.stock-items.*') }"
                        >
                            Inventario
                        </Link>
                    </nav>
                </div>

                <div class="app-header__actions">
                    <button
                        v-if="canInstall"
                        type="button"
                        class="btn btn--secondary app-header__install"
                        @click="promptInstall"
                    >
                        Instalar app
                    </button>
                    <span v-else-if="showIosInstallHint" class="app-header__ios-hint">
                        En iPhone: toca compartir y luego "Agregar a pantalla de inicio"
                    </span>

                    <Link :href="route('donations.create')" class="app-header__cta">
                        <span class="app-header__cta-icon">+</span>
                        <span class="app-header__cta-label">Nueva donación</span>
                    </Link>

                    <Dropdown align="right">
                        <template #trigger>
                            <button type="button" class="app-header__user-trigger">
                                <span class="app-header__avatar">
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </span>
                                <span class="app-header__user-name">
                                    {{ $page.props.auth.user.name }} ▾
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

        <nav class="bottom-nav">
            <Link :href="route('dashboard')" class="bottom-nav__item">
                <span
                    class="bottom-nav__dot"
                    :class="{ 'bottom-nav__dot--active': route().current('dashboard') }"
                ></span>
                <span
                    class="bottom-nav__label"
                    :class="{ 'bottom-nav__label--active': route().current('dashboard') }"
                >
                    Panel
                </span>
            </Link>
            <Link :href="route('donations.index')" class="bottom-nav__item">
                <span
                    class="bottom-nav__dot"
                    :class="{ 'bottom-nav__dot--active': route().current('donations.index') }"
                ></span>
                <span
                    class="bottom-nav__label"
                    :class="{ 'bottom-nav__label--active': route().current('donations.index') }"
                >
                    Donaciones
                </span>
            </Link>
            <Link :href="route('donations.create')" class="bottom-nav__item">
                <span
                    class="bottom-nav__dot"
                    :class="{ 'bottom-nav__dot--active': route().current('donations.create') }"
                ></span>
                <span
                    class="bottom-nav__label"
                    :class="{ 'bottom-nav__label--active': route().current('donations.create') }"
                >
                    Nueva
                </span>
            </Link>
            <Link
                v-if="isAdmin($page.props.auth.user)"
                :href="route('admin.users.index')"
                class="bottom-nav__item"
            >
                <span
                    class="bottom-nav__dot"
                    :class="{ 'bottom-nav__dot--active': route().current('admin.users.*') }"
                ></span>
                <span
                    class="bottom-nav__label"
                    :class="{ 'bottom-nav__label--active': route().current('admin.users.*') }"
                >
                    Usuarios
                </span>
            </Link>
            <Link
                v-if="isAdmin($page.props.auth.user)"
                :href="route('admin.stock-items.index')"
                class="bottom-nav__item"
            >
                <span
                    class="bottom-nav__dot"
                    :class="{ 'bottom-nav__dot--active': route().current('admin.stock-items.*') }"
                ></span>
                <span
                    class="bottom-nav__label"
                    :class="{ 'bottom-nav__label--active': route().current('admin.stock-items.*') }"
                >
                    Inventario
                </span>
            </Link>
        </nav>
    </div>
</template>
