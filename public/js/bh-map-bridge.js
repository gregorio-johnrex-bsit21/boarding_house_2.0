/**
 * BHMap — talks to boarding_house_map.html inside an <iframe> using postMessage.
 *
 *   const map = new BHMap(iframeEl, {
 *     onReady:  m => m.setStatuses({ R15_Bed1_A: 'occupied', ... }),
 *     onSelect: d => console.log(d)   // { level:'map'|'room'|'bed', room, bed, meshIds:[A,B] }
 *   });
 *   map.setStatus('R15_Bed1_A', 'reserved');   // recolor one bunk
 *   map.focusBed('R15_Bed1_A', '📍 You Are Here'); // camera zoom + floating badge
 *   map.focusRoom(15); map.reset();
 *
 * Messages sent before the map is ready are queued automatically.
 */
class BHMap {
  constructor(iframe, { onReady, onSelect } = {}) {
    this.frame = iframe; this.ready = false; this.queue = [];
    this.origin = location.origin;
    window.addEventListener('message', (e) => {
      if (e.source !== iframe.contentWindow) return;
      const d = e.data;
      if (!d || d.source !== 'bh-map') return;
      if (d.type === 'ready' && !this.ready) {
        this.ready = true;
        this.queue.splice(0).forEach((m) => this._post(m));
        onReady && onReady(this);
      } else if (d.type === 'select' && onSelect) onSelect(d);
    });
    // If the map finished loading before this script ran, ask it to say 'ready' again.
    const ping = () => { try { this._post({ type: 'ping' }); } catch (e) {} };
    iframe.addEventListener('load', ping); setTimeout(ping, 800); setTimeout(ping, 2500);
  }
  _post(m) { this.frame.contentWindow.postMessage({ target: 'bh-map', ...m }, this.origin); }
  send(m) { this.ready ? this._post(m) : this.queue.push(m); }
  setStatuses(statuses) { this.send({ type: 'setStatuses', statuses }); }
  setStatus(mesh_id, status) { this.send({ type: 'setStatus', mesh_id, status }); }
  focusBed(mesh_id, badge) { this.send({ type: 'focusBed', mesh_id, badge }); }
  focusRoom(room) { this.send({ type: 'focusRoom', room }); }
  reset() { this.send({ type: 'reset' }); }
}
window.BHMap = BHMap;
