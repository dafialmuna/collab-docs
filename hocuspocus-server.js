import { Hocuspocus } from '@hocuspocus/server';
import * as Y from 'yjs';

const apiBaseUrl = 'http://127.0.0.1:8000';

const loadDocumentState = async (documentName) => {
  const response = await fetch(`${apiBaseUrl}/api/documents/${documentName}/state`);

  if (!response.ok) {
    return null;
  }

  const payload = await response.json();

  if (!payload?.state || !Array.isArray(payload.state) || payload.state.length === 0) {
    return null;
  }

  const ydoc = new Y.Doc();
  Y.applyUpdate(ydoc, Uint8Array.from(payload.state));
  return ydoc;
};

const server = new Hocuspocus({
  port: 1234,
  name: 'aryanadocs-ws',
  quiet: false,
  debounce: 500,
  maxDebounce: 1000,
  unloadImmediately: false,
  async onLoadDocument({ documentName }) {
    console.log(`📥 Memuat dokumen ID: ${documentName}...`);

    try {
      return await loadDocumentState(documentName);
    } catch (error) {
      console.error('Gagal load dokumen dari database:', error);
      return null;
    }
  },
  async onStoreDocument({ documentName, document }) {
    console.log(`💾 Menyimpan dokumen ID: ${documentName}...`);
    try {
      const state = Y.encodeStateAsUpdate(document);
      const body = {
        document_id: documentName,
        state: state ? Array.from(state) : [],
        user_id: null,
      };

      // Kirim ke Laravel (gunakan fetch global di Node v18+)
      await fetch('http://127.0.0.1:8000/api/documents/sync', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
      });

      console.log(`✅ Berhasil simpan dokumen ${documentName}`);
    } catch (error) {
      console.error('Gagal simpan ke database:', error);
    }
  },
});

(async () => {
  await server.listen();
  console.log('🚀 Hocuspocus WebSocket Server running on ws://localhost:1234');
})();