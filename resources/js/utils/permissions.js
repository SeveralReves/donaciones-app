// Espejo en el frontend de las Gates definidas en AppServiceProvider — solo
// para decidir qué mostrar en la UI. La autorización real siempre la hace
// el backend (Gate::authorize() / can: middleware); esto es puramente para
// no ofrecer acciones que el servidor va a rechazar de todas formas.

// Espejo de User::isAdminOrAbove() en el backend: super_admin tiene todo el
// acceso de un admin normal.
export function isAdmin(user) {
    return user.rol === 'admin' || user.rol === 'super_admin';
}

/**
 * Espejo de la Gate 'create-donations': los voluntarios no registran
 * donaciones nuevas.
 */
export function canCreateDonations(user) {
    return user.rol !== 'voluntario';
}

/**
 * Espejo de la Gate 'view-stock': quien ya puede administrar el inventario
 * (isAdmin) más un voluntario, que solo puede verlo — nunca crear ni
 * modificar insumos (eso sigue siendo isAdmin-only, ver manage-stock).
 */
export function canViewStock(user) {
    return isAdmin(user) || user.rol === 'voluntario';
}

/**
 * Espejo de la Gate 'access-children-module': módulo aparte del resto de la
 * app, ni siquiera isAdmin() da acceso por defecto — solo
 * can_access_children_module explícito, o super_admin (acceso a todo).
 */
export function canAccessChildrenModule(user) {
    return user.rol === 'super_admin' || Boolean(user.can_access_children_module);
}
