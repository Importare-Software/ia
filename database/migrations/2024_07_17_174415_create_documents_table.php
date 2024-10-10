<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //---------CORRER DIRECTO EN LA DB--------//
        /* -- Asegurarse de que las extensiones necesarias están instaladas
        CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
        CREATE EXTENSION IF NOT EXISTS vector;
            
        -- Crear la tabla 'documents'
        CREATE TABLE public.documents (
            id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
            title text,
            content text,
            embedding vector(1536),
            metadata jsonb,
            user_id bigint NOT NULL,
            document_group_id uuid NOT NULL,
            FOREIGN KEY (user_id) REFERENCES public.users(id)
        );
        
        -- Crear un índice para acelerar las búsquedas vectoriales
        CREATE INDEX documents_embedding_idx ON public.documents USING ivfflat (embedding) WITH (lists = 100);
        
        -- Crear un índice GIN para búsquedas en metadatos
        CREATE INDEX documents_metadata_idx ON public.documents USING gin (metadata jsonb_path_ops);
        
        -- Crear índices para mejorar el rendimiento de las búsquedas y joins en 'user_id' y 'document_group_id'
        CREATE INDEX idx_user_id ON public.documents (user_id);
        CREATE INDEX idx_document_group_id ON public.documents (document_group_id); */

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
