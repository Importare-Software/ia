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
    public function upload(Request $request)
    {
        Log::info('Iniciando el proceso de carga del documento');

        $request->validate([
            'document' => 'required'
        ]);
        Log::info('Archivo validado correctamente');

        try {
            $filename = $request->document->getClientOriginalName();
            Log::info('Archivo recibido', ['filename' => $filename]);

            $markdownContent = file_get_contents($request->document->getRealPath());
            $contentSections = $this->parseContentIntoSections($markdownContent);

            if (empty($contentSections)) {
                Log::error('No se encontraron secciones para procesar');
                throw new \Exception("No se encontraron secciones para procesar.");
            }

            foreach ($contentSections as $section) {
                Log::info('Procesando sección', ['content' => $section['content'], 'tags' => $section['tags']]);
                $tags = $section['tags'];
                $tagsVector = $this->vectorizeContent(implode(" ", $tags));
                $contentVector = $this->vectorizeContent($section['content']);

                $document = new Document();
                $document->section = json_encode($section['tags']);;
                $document->content = json_encode($section['content']);
                $document->vector = json_encode($contentVector);
                $document->tags = json_encode($tagsVector);

                if (!$document->save()) {
                    Log::error('Error al guardar el documento en la base de datos');
                    throw new \Exception("Error al guardar el documento en la base de datos");
                }

                Log::info('Sección procesada y guardada', ['tags' => $tags]);
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
        $parsedown = new Parsedown();
        $content = $parsedown->text($markdownContent);
        // Dividir contenido utilizando el tag <h2> como punto de inicio para cada sección
        $sections = preg_split('/(?=<h2>)/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $taggedSections = [];

        foreach ($sections as $section) {
            // Buscar la primera aparición de </h2> para separar el título del contenido
            $endOfTitlePos = strpos($section, '</h2>');
            $titleWithH2 = substr($section, 0, $endOfTitlePos + 5); // +5 para incluir </h2>
            $title = strip_tags($titleWithH2); // Remover tags HTML para obtener solo el texto del título
            $sectionContent = trim(substr($section, $endOfTitlePos + 5)); // +5 para comenzar después del </h2>

            $taggedSections[] = [
                'content' => $sectionContent,
                'tags' => [trim($title)]
            ];
        }

        return $taggedSections;
    }


    private function vectorizeContent($content)
    {
        Log::info('Iniciando la vectorización del contenido');

        $client = new Client();
        $response = $client->post('https://api.openai.com/v1/embeddings', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'input' => $content,
                'model' => 'text-embedding-3-large',
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
}
