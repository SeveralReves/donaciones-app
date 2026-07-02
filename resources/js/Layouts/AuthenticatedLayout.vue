<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';

const isAdmin = (user) => user.rol === 'admin';
</script>

<template>
    <div class="min-h-screen pb-20 md:pb-0">
        <header class="sticky top-0 z-20 bg-[#0f5132]">
            <div class="mx-auto flex h-[62px] max-w-[1180px] items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-4 md:gap-7">
                    <Link :href="route('dashboard')" class="flex shrink-0 items-center gap-[11px]">
                        <span class="flex h-9 w-9 items-center justify-center rounded-[10px] bg-[#3ec37a]">
                            <span class="relative block h-[15px] w-[15px]">
                                <span class="absolute left-0 top-[6px] h-[3px] w-[15px] rounded-sm bg-[#0f5132]"></span>
                                <span class="absolute left-[6px] top-0 h-[15px] w-[3px] rounded-sm bg-[#0f5132]"></span>
                            </span>
                        </span>
                        <span class="hidden leading-tight sm:block">
                            <span class="block text-[17px] font-bold tracking-tight text-white">
                                {{ $page.props.app?.name ?? 'Control de Donaciones' }}
                            </span>
                        </span>
                    </Link>

                    <nav class="hidden items-center gap-1 md:flex">
                        <Link
                            :href="route('dashboard')"
                            class="rounded-lg px-[15px] py-2 text-sm"
                            :class="route().current('dashboard')
                                ? 'bg-white/15 font-semibold text-white'
                                : 'font-medium text-[#bfe0cd] hover:text-white'"
                        >
                            Panel
                        </Link>
                        <Link
                            :href="route('donations.index')"
                            class="rounded-lg px-[15px] py-2 text-sm"
                            :class="route().current('donations.*')
                                ? 'bg-white/15 font-semibold text-white'
                                : 'font-medium text-[#bfe0cd] hover:text-white'"
                        >
                            Donaciones
                        </Link>
                        <Link
                            v-if="isAdmin($page.props.auth.user)"
                            :href="route('admin.users.index')"
                            class="rounded-lg px-[15px] py-2 text-sm"
                            :class="route().current('admin.users.*')
                                ? 'bg-white/15 font-semibold text-white'
                                : 'font-medium text-[#bfe0cd] hover:text-white'"
                        >
                            Usuarios
                        </Link>
                    </nav>
                </div>

                <div class="flex items-center gap-3 sm:gap-[14px]">
                    <Link
                        :href="route('donations.create')"
                        class="flex items-center gap-[7px] whitespace-nowrap rounded-[10px] bg-[#3ec37a] px-3 py-[10px] text-[13px] font-bold text-[#0f5132] sm:px-4 sm:text-sm"
                    >
                        <span class="text-base leading-none">+</span>
                        <span class="hidden sm:inline">Nueva donación</span>
                    </Link>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button type="button" class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/[.18] text-[13px] font-semibold text-white">
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </span>
                                <span class="hidden text-[13px] font-medium text-[#d6ebde] md:inline">
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

        <header v-if="$slots.header" class="bg-white shadow-sm">
            <div class="mx-auto max-w-[1180px] px-4 py-6 sm:px-6">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>

        <nav class="fixed inset-x-0 bottom-0 z-30 flex border-t border-[#e2ebe7] bg-white px-1.5 pb-3 pt-2 md:hidden">
            <Link :href="route('dashboard')" class="flex-1 text-center">
                <span
                    class="mx-auto mb-[5px] block h-[5px] w-[5px] rounded-full"
                    :class="route().current('dashboard') ? 'bg-[#148f5b]' : 'bg-transparent'"
                ></span>
                <span
                    class="text-[11px]"
                    :class="route().current('dashboard') ? 'font-bold text-[#148f5b]' : 'font-medium text-[#8a969a]'"
                >
                    Panel
                </span>
            </Link>
            <Link :href="route('donations.index')" class="flex-1 text-center">
                <span
                    class="mx-auto mb-[5px] block h-[5px] w-[5px] rounded-full"
                    :class="route().current('donations.index') ? 'bg-[#148f5b]' : 'bg-transparent'"
                ></span>
                <span
                    class="text-[11px]"
                    :class="route().current('donations.index') ? 'font-bold text-[#148f5b]' : 'font-medium text-[#8a969a]'"
                >
                    Donaciones
                </span>
            </Link>
            <Link :href="route('donations.create')" class="flex-1 text-center">
                <span
                    class="mx-auto mb-[5px] block h-[5px] w-[5px] rounded-full"
                    :class="route().current('donations.create') ? 'bg-[#148f5b]' : 'bg-transparent'"
                ></span>
                <span
                    class="text-[11px]"
                    :class="route().current('donations.create') ? 'font-bold text-[#148f5b]' : 'font-medium text-[#8a969a]'"
                >
                    Nueva
                </span>
            </Link>
            <Link
                v-if="isAdmin($page.props.auth.user)"
                :href="route('admin.users.index')"
                class="flex-1 text-center"
            >
                <span
                    class="mx-auto mb-[5px] block h-[5px] w-[5px] rounded-full"
                    :class="route().current('admin.users.*') ? 'bg-[#148f5b]' : 'bg-transparent'"
                ></span>
                <span
                    class="text-[11px]"
                    :class="route().current('admin.users.*') ? 'font-bold text-[#148f5b]' : 'font-medium text-[#8a969a]'"
                >
                    Usuarios
                </span>
            </Link>
        </nav>
    </div>
</template>
