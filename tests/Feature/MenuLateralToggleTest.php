<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * El estado del menú lateral vive en el layout compartido, así que estas
 * pruebas atacan la pantalla completa: el marcado se valida sobre el HTML que
 * llega al navegador, no sobre el componente que lo contiene.
 *
 * El plegado lo lleva Flux (`collapsible`), de modo que lo que se comprueba
 * aquí es el contrato que el layout le pide a Flux: que el botón esté en la
 * cabecera del propio menú y que la barra superior no lo repita.
 */
beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    $this->actingAs($this->admin);
});

test('el botón que pliega el menú vive en su cabecera, junto a la marca', function () {
    $html = $this->get(route('dashboard'))->assertOk()->getContent();

    preg_match('/<ui-sidebar[\s>].*?<\/ui-sidebar>/s', $html, $menu);
    preg_match('/<header[\s>].*?<\/header>/s', $html, $barra);

    $menu = $menu[0] ?? '';
    $barra = $barra[0] ?? '';

    expect($menu)
        ->toContain('NovaStay')
        ->toContain('data-flux-sidebar-collapse')
        ->toContain('Mostrar u ocultar el menú lateral');

    // Sale después de la marca, o sea a su derecha, y no antes.
    expect(strrpos($menu, 'data-flux-sidebar-collapse'))
        ->toBeGreaterThan(strrpos($menu, 'NovaStay'));

    // La barra superior ya no lo repite: el control es único.
    expect($barra)->not->toContain('data-flux-sidebar-collapse');
});

test('el menú lateral se recoge a un carril de iconos en escritorio', function () {
    $html = $this->get(route('dashboard'))->assertOk()->getContent();

    preg_match('/<ui-sidebar[\s>][^>]*>/s', $html, $coincidencias);

    $sidebar = $coincidencias[0] ?? '';

    // `collapsible` es lo que hace que Flux publique
    // `data-flux-sidebar-collapsed-desktop`, y ese atributo es lo que activa
    // el carril de `w-14` con los iconos del menú centrados.
    expect($sidebar)
        ->toContain('id="menu-lateral"')
        ->toContain('data-flux-sidebar')
        ->toContain('collapsible="true"')
        ->toContain('data-flux-sidebar-collapsed-desktop:w-14')
        ->toContain('data-flux-sidebar-collapsed-desktop:px-2');

    // En el carril el rótulo se retira y queda el logotipo con el botón, que
    // es lo que devuelve el ancho completo al menú.
    expect($html)
        ->toContain('in-data-flux-sidebar-collapsed-desktop:hidden')
        ->toContain('in-data-flux-sidebar-collapsed-desktop:px-0');
});

test('el menú lateral no repite el cambio de tema ni el perfil del usuario', function () {
    $html = $this->get(route('dashboard'))->assertOk()->getContent();

    preg_match('/<ui-sidebar[\s>].*?<\/ui-sidebar>/s', $html, $menu);

    $menu = $menu[0] ?? '';

    expect($menu)
        ->not->toContain('role="switch"')
        ->not->toContain('data-flux-profile');

    // Y que de verdad no están en ninguna parte, ni aquí ni en la barra.
    expect(substr_count($html, 'role="switch"'))->toBe(1)
        ->and(substr_count($html, 'data-flux-profile'))->toBe(1);
});

test('la barra superior conserva el cambio de tema y el perfil del usuario', function () {
    $html = $this->get(route('dashboard'))->assertOk()->getContent();

    preg_match('/<header[\s>].*?<\/header>/s', $html, $barra);

    $barra = $barra[0] ?? '';

    expect($barra)
        ->toContain('role="switch"')
        ->toContain('data-flux-profile')
        ->toContain(auth()->user()->email);
});

test('el menú lateral también sigue siendo una capa en móvil', function () {
    $html = $this->get(route('dashboard'))->assertOk()->getContent();

    // En móvil manda Flux: su propio toggle abre y cierra el menú superpuesto,
    // así que el comportamiento existente no se toca.
    expect($html)
        ->toContain('data-flux-sidebar-toggle')
        ->toContain('data-flux-sidebar-backdrop');
});
