<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Parsedown;

class DocumentController extends Controller
{
    protected $supabase;
    protected $embeddingModel;

    public function __construct()
    {
        $this->supabase = new Client([
            'base_uri' => env('SUPABASE_URL'),
            'headers'  => [
                'apikey'        => env('SUPABASE_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
                'Content-Type'  => 'application/json'
            ]
        ]);

        $this->embeddingModel = 'text-embedding-3-small'; // Modelo de embeddings recomendado
    }

    public function showUploadForm()
    {
        $user = auth()->user();
        $hasDocument = Document::where('user_id', $user->id)->exists();

        return view('upload-data', ['hasDocument' => $hasDocument]);
    }

    public function upload(Request $request)
    {
        Log::info('Iniciando el proceso de carga del documento');

        $request->validate([
            'document' => 'required|file'
        ]);
        Log::info('Archivo validado correctamente');

        try {
            $user = auth()->user();
            $filename = $request->document->getClientOriginalName();
            Log::info('Archivo recibido', ['filename' => $filename]);

            $markdownContent = file_get_contents($request->document->getRealPath());
            $contentSections = $this->parseContentIntoSections($markdownContent);

            if (empty($contentSections)) {
                Log::error('No se encontraron secciones para procesar');
                throw new \Exception("No se encontraron secciones para procesar.");
            }

            $documentGroupId = (string) \Illuminate\Support\Str::uuid();

            foreach ($contentSections as $section) {
                Log::info('Procesando sección', ['title' => $section['title']]);

                // Generar embedding combinado del título y contenido
                $embedding = $this->vectorizeContent($section['title'] . ' ' . $section['content']);

                // Preparar metadatos (etiquetas)
                $metadata = [
                    'tags' => $section['tags']
                ];

                // Guardar en Supabase
                $response = $this->supabase->request('POST', 'rest/v1/documents', [
                    'json' => [
                        'title'     => $section['title'],
                        'content'   => $section['content'],
                        'embedding' => $embedding,
                        'metadata'  => $metadata,
                        'user_id'   => $user->id,
                        'document_group_id' => $documentGroupId
                    ]
                ]);

                Log::info('Sección procesada y guardada', ['title' => $section['title']]);
            }

            Log::info('Documento guardado en la base de datos con secciones etiquetadas y vectorizadas');
            return redirect()->back()->with('success', 'Documento cargado y procesado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al procesar el documento', ['exception' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error al cargar el documento. Por favor, inténtelo de nuevo.');
        }
    }

    private function parseContentIntoSections($markdownContent)
    {
        $lines = preg_split('/\r\n|\r|\n/', $markdownContent);
        $sections = [];
        $currentTitle = '';
        $currentContent = '';

        foreach ($lines as $line) {
            if (preg_match('/^##\s*(.+)$/', $line, $matches)) {
                // Si hay un título actual, guardamos la sección anterior
                if ($currentTitle && $currentContent) {
                    $sections[] = [
                        'title'   => trim($currentTitle),
                        'tags'    => $this->extractTags($currentTitle),
                        'content' => trim($currentContent)
                    ];
                }
                // Iniciamos una nueva sección
                $currentTitle = $matches[1];
                $currentContent = '';
            } else {
                // Acumulamos el contenido
                $currentContent .= $line . "\n";
            }
        }

        // Agregar la última sección
        if ($currentTitle && $currentContent) {
            $sections[] = [
                'title'   => trim($currentTitle),
                'tags'    => $this->extractTags($currentTitle),
                'content' => trim($currentContent)
            ];
        }

        return $sections;
    }

    private function extractTags($title)
    {
        // Dividir el título por '|' y obtener las etiquetas
        $tags = explode('|', $title);
        // Limpiar las etiquetas
        $tags = array_map('trim', $tags);
        return $tags;
    }

    private function vectorizeContent($content)
    {
        Log::info('Iniciando la vectorización del contenido');

        $client = new Client();
        $response = $client->post('https://api.openai.com/v1/embeddings', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'input' => $content,
                'model' => $this->embeddingModel,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        if (isset($data['data'][0]['embedding'])) {
            Log::info('Vectorización completada');
            return $data['data'][0]['embedding'];
        } else {
            Log::error('Error en la vectorización', ['response' => $response->getBody()]);
            return null;
        }
    }

    public function delete()
    {
        try {
            $user = auth()->user();

            $document = Document::where('user_id', $user->id)->first();

            if (!$document) {
                return redirect()->back()->with('error', 'No se encontró ningún documento asociado al usuario para eliminar.');
            }

            Document::where('document_group_id', $document->document_group_id)->delete();

            return redirect()->back()->with('success', 'Todos los fragmentos del documento han sido eliminados correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el documento: ' . $e->getMessage());
        }
    }
}
