<?php

namespace App\Livewire\Habitaciones;

use App\Models\Habitacion;
use App\Models\TipoHabitacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use RuntimeException;

/**
 * Formulario de alta y edición dentro del modal. Se carga de forma diferida
 * para no consultar el catálogo de tipos de habitación hasta que el modal se
 * abre.
 *
 * El modal es un <dialog> nativo abierto por Flux desde el navegador, así que
 * este componente no lleva booleanos de visibilidad: recibe el modo de la
 * operación por el evento `abrir-formulario` y avisa con
 * `habitacion-guardada` cuando la escritura termina.
 *
 * La fotografía se sube como archivo, se convierte a WebP en el servidor y en
 * la base de datos solo se guarda la ruta del archivo resultante.
 */
#[Lazy]
class FormModal extends Component
{
    use WithFileUploads;

    /**
     * Calidad con la que se codifican las fotografías en WebP.
     */
    public const CALIDAD_WEBP = 82;

    /**
     * Formatos aceptados para la fotografía de la habitación.
     *
     * @var list<string>
     */
    public const FORMATOS_FOTO = ['jpg', 'jpeg', 'png', 'webp'];

    public ?int $habitacionId = null;

    public string $numero_habitacion = '';

    public string $tipo_habitacion_id = '';

    public string $estado = 'Disponible';

    public string $piso = '1';

    public string $descripcion = '';

    /**
     * Fotografía recién subida, todavía sin convertir.
     */
    public ?TemporaryUploadedFile $foto = null;

    /**
     * Ruta de la fotografía que ya tiene guardada la habitación, si la tiene.
     */
    public ?string $fotoGuardada = null;

    public function mount(): void
    {
        $this->cargarFormulario();
    }

    /**
     * Catálogo de tipos de habitación, consultado solo al abrir el modal.
     *
     * @return Collection<int, TipoHabitacion>
     */
    #[Computed]
    public function tipos(): Collection
    {
        return TipoHabitacion::orderBy('nombre')->get();
    }

    /**
     * URL pública de la fotografía ya guardada, para mostrarla mientras no se
     * elige una nueva.
     */
    #[Computed]
    public function urlFotoGuardada(): ?string
    {
        if (blank($this->fotoGuardada)) {
            return null;
        }

        return Storage::disk(Habitacion::DISCO_FOTOS)->url($this->fotoGuardada);
    }

    /**
     * Rellena la descripción con la del tipo de habitación elegido. La capacidad
     * y el precio por noche no se piden: se heredan del tipo de habitación.
     */
    public function updatedTipoHabitacionId(): void
    {
        $tipo = $this->tipo_habitacion_id !== ''
            ? TipoHabitacion::find($this->tipo_habitacion_id)
            : null;

        if ($tipo === null) {
            return;
        }

        $this->descripcion = $tipo->descripcion ?? '';
    }

    public function guardar(): void
    {
        $this->validate([
            'numero_habitacion' => ['required', 'string', 'max:10', 'unique:habitaciones,numero_habitacion,'.$this->habitacionId],
            'tipo_habitacion_id' => ['required', 'exists:tipos_habitacion,id'],
            'estado' => ['required', Rule::in(Habitacion::ESTADOS)],
            'piso' => ['required', 'integer', 'min:1'],
            'foto' => ['nullable', 'image', 'mimes:'.implode(',', self::FORMATOS_FOTO), 'max:5120'],
        ]);

        $habitacion = $this->habitacionId === null
            ? new Habitacion
            : Habitacion::findOrFail($this->habitacionId);

        $fotografiaAnterior = $habitacion->foto;

        $datos = [
            'numero_habitacion' => $this->numero_habitacion,
            'tipo_habitacion_id' => $this->tipo_habitacion_id,
            'estado' => $this->estado,
            'piso' => $this->piso,
        ];

        if ($this->foto !== null) {
            $datos['foto'] = $this->convertirFotoAWebp($this->foto);
        }

        $esNueva = ! $habitacion->exists;

        $habitacion->fill($datos)->save();

        $this->descartarFotografiaReemplazada($datos['foto'] ?? null, $fotografiaAnterior);

        $this->cargarFormulario();

        $this->dispatch(
            'habitacion-guardada',
            mensaje: $esNueva
                ? 'Habitación creada correctamente.'
                : 'Habitación actualizada correctamente.'
        );
    }

    /**
     * Prepara el formulario para creación o edición.
     *
     * Lo dispara el botón de la interfaz con un evento de Livewire. Solo este
     * componente reacciona, así que el contenedor no se re-renderiza al abrir y
     * el <dialog> conserva su estado nativo de `open`.
     */
    #[On('abrir-formulario')]
    public function preparar(?int $id): void
    {
        $this->habitacionId = $id;

        $this->cargarFormulario();
    }

    /**
     * Convierte la fotografía subida a WebP, la guarda en el disco público y
     * devuelve la ruta relativa del archivo resultante.
     */
    private function convertirFotoAWebp(TemporaryUploadedFile $foto): string
    {
        $base = Str::slug($this->numero_habitacion) ?: 'habitacion';
        $ruta = Habitacion::CARPETA_FOTOS.'/'.$base.'-'.Str::random(8).'.webp';

        $contenido = $this->gestorImagen()
            ->read($foto->getRealPath())
            ->encodeByExtension('webp', quality: self::CALIDAD_WEBP)
            ->toString();

        Storage::disk(Habitacion::DISCO_FOTOS)->put($ruta, $contenido);

        return $ruta;
    }

    /**
     * Borra del disco la fotografía que quedó reemplazada por la nueva. Si no
     * se subió ninguna fotografía nueva, la anterior se conserva.
     */
    private function descartarFotografiaReemplazada(?string $fotoNueva, ?string $fotoAnterior): void
    {
        if (blank($fotoNueva) || blank($fotoAnterior) || $fotoAnterior === $fotoNueva) {
            return;
        }

        Storage::disk(Habitacion::DISCO_FOTOS)->delete($fotoAnterior);
    }

    /**
     * Gestor de imágenes de Intervention sobre el driver disponible en el
     * servidor.
     */
    private function gestorImagen(): ImageManager
    {
        $driver = match (true) {
            extension_loaded('imagick') => new ImagickDriver,
            extension_loaded('gd') => new GdDriver,
            default => throw new RuntimeException(
                'No se pudo procesar la fotografía: el servidor necesita la extensión GD o Imagick de PHP.'
            ),
        };

        return new ImageManager($driver);
    }

    private function cargarFormulario(): void
    {
        $habitacion = $this->habitacionId === null
            ? null
            : Habitacion::with('tipoHabitacion')->findOrFail($this->habitacionId);

        $this->resetValidation();

        $this->foto = null;

        if ($habitacion === null) {
            $this->habitacionId = null;
            $this->numero_habitacion = '';
            $this->tipo_habitacion_id = '';
            $this->estado = 'Disponible';
            $this->piso = '1';
            $this->descripcion = '';
            $this->fotoGuardada = null;

            return;
        }

        $this->numero_habitacion = $habitacion->numero_habitacion;
        $this->tipo_habitacion_id = (string) $habitacion->tipo_habitacion_id;
        $this->estado = $habitacion->estado;
        $this->piso = (string) $habitacion->piso;
        $this->descripcion = $habitacion->tipoHabitacion?->descripcion ?? '';
        $this->fotoGuardada = $habitacion->foto;
    }
}
