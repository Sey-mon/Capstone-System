import numpy as np
from sentence_transformers import SentenceTransformer
import faiss
from typing import List, Tuple
from data_manager import data_manager
import os
import pickle
import hashlib

class EmbeddingSearcher:
    def __init__(self, model_name: str = "sentence-transformers/all-MiniLM-L6-v2"):
        """Initialize the embedding model and FAISS index."""
        self.model = SentenceTransformer(model_name)
        self.index = None
        self.chunks = []
        self.chunk_metadata = []  # Store kb_id and other info for each chunk
        self.cache_dir = "embeddings_cache"
        self.index_file = os.path.join(self.cache_dir, "faiss_index.idx")
        self.chunks_file = os.path.join(self.cache_dir, "chunks.pkl")
        self.metadata_file = os.path.join(self.cache_dir, "metadata.pkl")
        
        # Create cache directory if it doesn't exist
        if not os.path.exists(self.cache_dir):
            os.makedirs(self.cache_dir)
        
        # Try to load cached embeddings on initialization
        self._load_embeddings()
    
    def _get_knowledge_base_hash(self):
        """Generate hash of knowledge base to detect changes.
        Only includes content fields (pdf_text, ai_summary, pdf_name) and excludes
        dynamic fields (timestamps, user info) to prevent unnecessary cache invalidation.
        """
        knowledge_base = data_manager.get_knowledge_base()
        
        # Build a stable hash using only content fields, sorted by kb_id for consistency
        content_items = []
        for kb_id in sorted(knowledge_base.keys(), key=lambda x: str(x)):
            entry = knowledge_base[kb_id]
            # Only include fields that represent actual content
            content_tuple = (
                str(kb_id),
                entry.get('pdf_text', ''),
                entry.get('ai_summary', ''),
                entry.get('pdf_name', '')
            )
            content_items.append(content_tuple)
        
        kb_string = str(content_items)
        return hashlib.md5(kb_string.encode()).hexdigest()
    
    def _save_embeddings(self):
        """Save FAISS index and chunks to disk."""
        if self.index is not None:
            faiss.write_index(self.index, self.index_file)
        
        # Always save chunks and metadata (can be empty)
        with open(self.chunks_file, 'wb') as f:
            pickle.dump(self.chunks, f)
            
        with open(self.metadata_file, 'wb') as f:
            pickle.dump(self.chunk_metadata, f)
            
        # Save knowledge base hash
        hash_file = os.path.join(self.cache_dir, "kb_hash.txt")
        with open(hash_file, 'w') as f:
            f.write(self._get_knowledge_base_hash())
        
        print("Embeddings saved to cache.")
    
    def _load_embeddings(self):
        """Load FAISS index and chunks from disk if available and valid."""
        hash_file = os.path.join(self.cache_dir, "kb_hash.txt")
        
        # Check if minimum cache files exist (chunks and metadata are always required)
        if not all(os.path.exists(f) for f in [self.chunks_file, self.metadata_file, hash_file]):
            return False
        
        # Check if knowledge base changed
        try:
            with open(hash_file, 'r') as f:
                cached_hash = f.read().strip()
            current_hash = self._get_knowledge_base_hash()
            
            if cached_hash != current_hash:
                print("Knowledge base changed, rebuilding embeddings...")
                return False
        except:
            return False
        
        try:
            # Load chunks and metadata (always present)
            with open(self.chunks_file, 'rb') as f:
                self.chunks = pickle.load(f)
                
            with open(self.metadata_file, 'rb') as f:
                self.chunk_metadata = pickle.load(f)
            
            # Load FAISS index if it exists (may not exist for empty KB)
            if os.path.exists(self.index_file):
                self.index = faiss.read_index(self.index_file)
                print(f"Loaded embeddings from cache ({len(self.chunks)} chunks).")
            else:
                self.index = None
                if len(self.chunks) == 0:
                    print("Loaded empty embeddings cache (no PDF content in knowledge base).")
                else:
                    # This shouldn't happen - chunks exist but no index
                    print("Warning: Chunks found but index missing. Will rebuild.")
                    return False
            
            return True
        except Exception as e:
            print(f"Failed to load cache: {str(e)}, rebuilding...")
            return False
        
    def build_embeddings_from_knowledge_base(self, batch_size: int = 128, force_rebuild: bool = False):
        """Build embeddings for all PDF texts in knowledge base and save to cache.
        
        Args:
            batch_size: Batch size for encoding
            force_rebuild: If True, rebuild even if cache is valid
        """
        # Check if embeddings are already available and valid
        if not force_rebuild and self.index is not None and len(self.chunks) > 0:
            # Check if knowledge base hash matches
            hash_file = os.path.join(self.cache_dir, "kb_hash.txt")
            if os.path.exists(hash_file):
                try:
                    with open(hash_file, 'r') as f:
                        cached_hash = f.read().strip()
                    current_hash = self._get_knowledge_base_hash()
                    if cached_hash == current_hash:
                        print(f"Using existing embeddings ({len(self.chunks)} chunks).")
                        return True
                except:
                    pass
        
        print("Building embeddings from knowledge base...")
        knowledge_base = data_manager.get_knowledge_base()
        
        all_chunks = []
        all_metadata = []
        
        # Extract and chunk all PDF texts
        for kb_id, kb_entry in knowledge_base.items():
            pdf_text = kb_entry.get('pdf_text', '')
            if pdf_text:
                # Chunk the PDF text
                chunks = data_manager.chunk_pdf_text_with_overlap(pdf_text)
                
                for chunk in chunks:
                    if chunk.strip():  # Only add non-empty chunks
                        all_chunks.append(chunk.strip())
                        all_metadata.append({
                            'kb_id': kb_id,
                            'pdf_name': kb_entry.get('pdf_name', ''),
                            'ai_summary': kb_entry.get('ai_summary', '')
                        })
        
        if not all_chunks:
            print("No PDF texts found in knowledge base. Creating empty index.")
            # Create an empty but valid index so the system can continue without PDF guidance
            self.chunks = []
            self.chunk_metadata = []
            self.index = None
            # Save empty state to prevent repeated attempts
            self._save_embeddings()
            return True  # Return True to indicate successful handling of empty KB
            
        print(f"Processing {len(all_chunks)} chunks...")
        
        # Create embeddings in batches
        embeddings = []
        for i in range(0, len(all_chunks), batch_size):
            batch = all_chunks[i:i + batch_size]
            batch_embeddings = self.model.encode(batch, show_progress_bar=True)
            embeddings.extend(batch_embeddings)
        
        # Convert to numpy array
        embeddings = np.array(embeddings).astype('float32')
        
        # Create FAISS index
        dimension = embeddings.shape[1]
        self.index = faiss.IndexFlatIP(dimension)  # Inner product (cosine similarity)
        
        # Normalize embeddings for cosine similarity
        faiss.normalize_L2(embeddings)
        self.index.add(embeddings)
        
        # Store chunks and metadata
        self.chunks = all_chunks
        self.chunk_metadata = all_metadata
        
        print(f"Created FAISS index with {len(all_chunks)} chunks.")
        
        # Save to cache
        self._save_embeddings()
        return True
    
    def create_embeddings_from_knowledge_base(self, batch_size: int = 128):
        """Deprecated: Use build_embeddings_from_knowledge_base() instead."""
        print("Warning: create_embeddings_from_knowledge_base() is deprecated. Use build_embeddings_from_knowledge_base()")
        return self.build_embeddings_from_knowledge_base(batch_size)
    
    def check_embeddings_status(self):
        """Check if embeddings are available and provide status information."""
        cache_files_exist = all(os.path.exists(f) for f in [self.index_file, self.chunks_file, self.metadata_file])

        if not cache_files_exist:
            return {
                "status": "no_cache",
                "message": "No cached embeddings found. Run build_embeddings_from_knowledge_base() to create them.",
                "chunks_count": 0,
                "per_pdf": {}
            }

        # Ensure cache is loaded
        if self.index is None or len(self.chunks) == 0 or len(self.chunk_metadata) == 0:
            loaded = self._load_embeddings()
            if not loaded:
                return {
                    "status": "cache_invalid",
                    "message": "Cached embeddings are invalid or outdated. Run build_embeddings_from_knowledge_base() to rebuild.",
                    "chunks_count": 0,
                    "per_pdf": {}
                }

        # Build per-PDF coverage: for each KB entry that has pdf_text, compute expected chunks
        knowledge_base = data_manager.get_knowledge_base()
        per_pdf = {}

        # Count embedded chunks per kb_id from metadata
        embedded_counts = {}
        for md in self.chunk_metadata:
            kb = md.get('kb_id')
            # normalize key to str for consistent mapping
            key = str(kb)
            embedded_counts[key] = embedded_counts.get(key, 0) + 1

        for kb_id, entry in knowledge_base.items():
            pdf_text = entry.get('pdf_text', '')
            pdf_name = entry.get('pdf_name', '')
            if not pdf_text:
                # No PDF text present in KB entry
                per_pdf[str(kb_id)] = {
                    'pdf_name': pdf_name,
                    'status': 'no_pdf_text',
                    'expected_chunks': 0,
                    'embedded_chunks': embedded_counts.get(str(kb_id), 0),
                    'missing_chunks': 0
                }
                continue

            # Chunk the pdf_text using the same chunking method used when creating embeddings
            try:
                expected_chunks_list = data_manager.chunk_pdf_text_with_overlap(pdf_text)
                expected_count = sum(1 for c in expected_chunks_list if c.strip())
            except Exception:
                # If chunking fails, mark unknown
                expected_count = None

            embedded = embedded_counts.get(str(kb_id), 0)
            missing = None
            status = 'unknown'
            if expected_count is None:
                status = 'chunking_failed'
            else:
                if embedded == 0:
                    status = 'not_embedded'
                elif embedded >= expected_count:
                    status = 'complete'
                    missing = 0
                else:
                    status = 'partial'
                    missing = expected_count - embedded

            per_pdf[str(kb_id)] = {
                'pdf_name': pdf_name,
                'status': status,
                'expected_chunks': expected_count,
                'embedded_chunks': embedded,
                'missing_chunks': missing
            }

        total_chunks = len(self.chunks)
        overall_status = 'ready'
        # If any KB has not_embedded or partial, mark overall as partial
        for info in per_pdf.values():
            if info.get('status') in ('not_embedded', 'partial', 'chunking_failed'):
                overall_status = 'partial'
                break

        return {
            'status': overall_status,
            'message': f'Embeddings cache loaded with {total_chunks} chunks.',
            'chunks_count': total_chunks,
            'per_pdf': per_pdf
        }
    
    def search_similar_chunks(self, query: str, k: int = 4) -> List[Tuple[str, float, dict]]:
        """Search for similar chunks using semantic similarity.
        Automatically builds embeddings if they're not available.
        Returns empty list if knowledge base is empty (which is normal).
        """
        # If index is not loaded, try to load from cache
        if self.index is None and len(self.chunks) == 0:
            if not self._load_embeddings():
                # If cache loading fails, try to build embeddings automatically
                print("Warning: No cached embeddings found. Building embeddings automatically...")
                try:
                    self.build_embeddings_from_knowledge_base()
                except Exception as e:
                    print(f"Error building embeddings: {str(e)}")
                    # Continue anyway - meal plans can work without PDF context
        
        # If still no index or chunks after attempting to build, return empty (not an error)
        if self.index is None or len(self.chunks) == 0:
            print("No PDF content available in knowledge base. Meal plan will be generated without PDF guidance.")
            return []
        
        # Encode query
        query_embedding = self.model.encode([query])
        query_embedding = np.array(query_embedding).astype('float32')
        
        # Normalize for cosine similarity
        faiss.normalize_L2(query_embedding)
        
        # Search (k most similar)
        scores, indices = self.index.search(query_embedding, min(k, len(self.chunks)))
        
        # Return results with chunks, scores, and metadata
        results = []
        for score, idx in zip(scores[0], indices[0]):
            if idx < len(self.chunks):  # Valid index
                results.append((
                    self.chunks[idx],
                    float(score),
                    self.chunk_metadata[idx]
                ))
        
        return results

    def reembed_missing_pdfs(self, batch_size: int = 128) -> dict:
        """Detect PDFs that are not fully embedded and embed only the missing ones.

        This function will:
        - Load current cache (index, chunks, metadata)
        - Determine which KB entries are missing or partial
        - For each missing/partial KB, re-chunk and embed the entire PDF text and append to index
        - Save updated index, chunks, and metadata

        Returns a summary dict with counts and per-kb results.
        """
        # Ensure KB is loaded
        self._load_embeddings()

        knowledge_base = data_manager.get_knowledge_base()
        if not knowledge_base:
            return {'status': 'no_kb', 'message': 'No knowledge base entries found', 'updated': False}

        # Count embedded chunks by kb_id
        embedded_counts = {}
        for md in self.chunk_metadata:
            kb = md.get('kb_id')
            key = str(kb)
            embedded_counts[key] = embedded_counts.get(key, 0) + 1

        to_process = []  # list of (kb_id, pdf_text, pdf_name)
        per_kb = {}
        for kb_id, entry in knowledge_base.items():
            pdf_text = entry.get('pdf_text', '')
            pdf_name = entry.get('pdf_name', '')
            if not pdf_text or not pdf_text.strip():
                per_kb[str(kb_id)] = {'status': 'no_pdf_text', 'expected': 0, 'embedded': embedded_counts.get(str(kb_id), 0)}
                continue

            expected_chunks = len([c for c in data_manager.chunk_pdf_text_with_overlap(pdf_text) if c.strip()])
            embedded = embedded_counts.get(str(kb_id), 0)
            if embedded >= expected_chunks and expected_chunks > 0:
                per_kb[str(kb_id)] = {'status': 'complete', 'expected': expected_chunks, 'embedded': embedded}
                continue

            # Not embedded or partial
            per_kb[str(kb_id)] = {'status': 'to_process', 'expected': expected_chunks, 'embedded': embedded}
            to_process.append((kb_id, pdf_text, pdf_name))

        if not to_process:
            return {'status': 'all_embedded', 'message': 'All knowledge base PDFs are already embedded', 'per_kb': per_kb, 'updated': False}

        # Prepare lists for new embeddings
        new_chunks = []
        new_metadata = []

        for kb_id, pdf_text, pdf_name in to_process:
            chunks = data_manager.chunk_pdf_text_with_overlap(pdf_text)
            for chunk in chunks:
                if chunk.strip():
                    new_chunks.append(chunk.strip())
                    new_metadata.append({
                        'kb_id': int(kb_id),
                        'pdf_name': pdf_name,
                        'ai_summary': knowledge_base.get(kb_id, {}).get('ai_summary', '')
                    })

        if not new_chunks:
            return {'status': 'no_new_chunks', 'message': 'No new chunks to embed', 'per_kb': per_kb, 'updated': False}

        # Create embeddings for new chunks in batches
        embeddings = []
        for i in range(0, len(new_chunks), batch_size):
            batch = new_chunks[i:i + batch_size]
            batch_embeddings = self.model.encode(batch, show_progress_bar=True)
            embeddings.extend(batch_embeddings)

        import numpy as _np
        new_embeddings = _np.array(embeddings).astype('float32')
        # normalize
        faiss.normalize_L2(new_embeddings)

        # If index not present, create a new index
        if self.index is None:
            dim = new_embeddings.shape[1]
            self.index = faiss.IndexFlatIP(dim)
            # If previously had chunks, ensure they are included (shouldn't happen if index None)

        # Append new embeddings to index
        try:
            self.index.add(new_embeddings)
        except Exception as e:
            return {'status': 'error_adding_index', 'message': str(e)}

        # Append new chunks and metadata
        self.chunks.extend(new_chunks)
        self.chunk_metadata.extend(new_metadata)

        # Save updated cache (will write kb_hash too)
        try:
            self._save_embeddings()
        except Exception as e:
            return {'status': 'error_saving', 'message': str(e)}

        # Recompute per_kb embedded counts for return
        updated_embedded_counts = {}
        for md in self.chunk_metadata:
            key = str(md.get('kb_id'))
            updated_embedded_counts[key] = updated_embedded_counts.get(key, 0) + 1

        for kb in per_kb.keys():
            per_kb[kb]['embedded_after'] = updated_embedded_counts.get(kb, 0)

        return {'status': 'success', 'message': f'Embedded {len(new_chunks)} new chunks for {len(to_process)} documents', 'per_kb': per_kb, 'updated': True}


def get_contextual_nutrition_guidance(patient_data: dict, context_type: str = "general", k: int = 4) -> str:
    """
    Unified function to get top-K similar nutrition guidance chunks using existing embedding model.
    
    Args:
        patient_data: Patient information dictionary
        context_type: Type of guidance needed ("analysis", "assessment", "meal_plan", "general")
        k: Number of top similar chunks to retrieve
    
    Returns:
        Formatted context string from knowledge base chunks
    """
    # Build query based on patient data and context type
    age_months = patient_data.get('age_months', 0)
    allergies = patient_data.get('allergies', '')
    medical_problems = patient_data.get('other_medical_problems', '')
    weight_status = patient_data.get('weight_for_age', '')
    height_status = patient_data.get('height_for_age', '')
    
    # Create targeted query based on context type and patient characteristics
    query_parts = []
    
    # Age-specific guidance
    if age_months <= 6:
        query_parts.append("exclusive breastfeeding infant nutrition 0-6 months")
    elif age_months <= 12:
        query_parts.append("complementary feeding introduction 6-12 months iron rich foods")
    elif age_months <= 24:
        query_parts.append("toddler nutrition 12-24 months feeding practices")
    else:
        query_parts.append("young child nutrition 2-5 years dietary guidelines")
    
    # Context-specific terms
    if context_type == "analysis":
        query_parts.append("nutritional assessment evaluation status")
    elif context_type == "assessment":
        query_parts.append("pediatric dietary assessment comprehensive evaluation")
    elif context_type == "meal_plan":
        query_parts.append("meal planning children nutrition guidelines")
    
    # Add condition-specific queries
    if allergies and allergies.lower() not in ['none', 'no', 'n/a', 'not specified']:
        query_parts.append(f"food allergies children {allergies} alternative foods")
    
    if medical_problems and medical_problems.lower() not in ['none', 'no', 'n/a', 'not specified']:
        query_parts.append(f"child nutrition {medical_problems} dietary management")
    
    # Growth-related queries
    if 'underweight' in weight_status.lower() or 'wasted' in weight_status.lower():
        query_parts.append("underweight children nutrition dense foods weight gain")
    elif 'overweight' in weight_status.lower():
        query_parts.append("overweight children healthy eating weight management")
        
    if 'stunted' in height_status.lower() or 'short' in height_status.lower():
        query_parts.append("stunting prevention linear growth nutrition")
    
    # Combine query parts
    query = " ".join(query_parts)
    
    try:
        # Use embedding searcher to get top-K similar chunks (only uses cached embeddings)
        results = embedding_searcher.search_similar_chunks(query, k=k)
        
        if results:
            # Filter results by similarity threshold and format
            relevant_chunks = []
            for chunk, score, metadata in results:
                if score > 0.4:  # Only include chunks with good similarity
                    # Add source information if available
                    source_info = f"(Source: {metadata.get('pdf_name', 'Unknown')})" if metadata.get('pdf_name') else ""
                    relevant_chunks.append(f"{chunk.strip()} {source_info}")
            
            if relevant_chunks:
                return f"\nEVIDENCE-BASED NUTRITION GUIDANCE:\n" + "\n---\n".join(relevant_chunks) + "\n"
        
        return ""  # Return empty string if no relevant guidance found
        
    except Exception as e:
        print(f"Error retrieving nutrition guidance: {str(e)}")
        print("Note: If no embeddings found, run 'python build_embeddings.py' to create them.")
        return ""  # Return empty string on error to avoid breaking the main functionality


# Global instance
embedding_searcher = EmbeddingSearcher()