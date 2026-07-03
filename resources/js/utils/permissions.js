// Espejo en el frontend de las Gates definidas en AppServiceProvider — solo
// para decidir qué mostrar en la UI. La autorización real siempre la hace
// el backend (Gate::authorize() / can: middleware); esto es puramente para
// no ofrecer acciones que el servidor va a rechazar de todas formas.

export function isAdmin(user) {
    return user.rol === 'admin';
}

/**
 * Espejo de la Gate 'create-donations': los voluntarios no registran
 * donaciones nuevas.
 */
export function canCreateDonations(user) {
    return user.rol !== 'voluntario';
}
