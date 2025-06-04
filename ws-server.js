const WebSocket = require('ws');
const mysql = require('mysql2/promise');

const pool = mysql.createPool({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'webportal'
});

const wss = new WebSocket.Server({ port: 8080 });
const clients = new Map();

wss.on('connection', async (ws, req) => {
  const params = new URL(req.url, `http://${req.headers.host}`).searchParams;
  const user = params.get('user');
  if(!user){ ws.close(); return; }
  clients.set(user, ws);

  ws.on('message', async message => {
    try {
      const data = JSON.parse(message.toString());
      if(data.type === 'message') {
        const [sRow] = await pool.query('SELECT id FROM users WHERE username=?', [user]);
        const [rRow] = await pool.query('SELECT id FROM users WHERE username=?', [data.to]);
        if(!sRow[0] || !rRow[0]) return;
        const sId = sRow[0].id;
        const rId = rRow[0].id;
        const [res] = await pool.query('INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)', [sId, rId, data.text]);
        const [row] = await pool.query('SELECT id, created_at FROM messages WHERE id=?', [res.insertId]);
        const msgRow = row[0];
        const payload = {type:'message', from:user, id:msgRow.id, text:data.text, time:msgRow.created_at, status:'sent'};
        ws.send(JSON.stringify(payload));
        const other = clients.get(data.to);
        if(other){
          other.send(JSON.stringify({...payload, status:'delivered'}));
        }
      } else if(data.type === 'typing') {
        const other = clients.get(data.to);
        if(other){
          other.send(JSON.stringify({type:'typing', from:user}));
        }
      } else if(data.type === 'seen') {
        await pool.query('UPDATE messages SET is_read=1 WHERE id=?', [data.id]);
        const other = clients.get(data.to);
        if(other){
          other.send(JSON.stringify({type:'seen', id:data.id, from:user}));
        }
      } else if(data.type === 'history') {
        const [sRow] = await pool.query('SELECT id FROM users WHERE username=?', [user]);
        const [rRow] = await pool.query('SELECT id FROM users WHERE username=?', [data.with]);
        if(!sRow[0] || !rRow[0]) return;
        const sId = sRow[0].id;
        const rId = rRow[0].id;
        const [rows] = await pool.query('SELECT id, sender_id, message, created_at, is_read FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY id', [sId, rId, rId, sId]);
        ws.send(JSON.stringify({type:'history', messages: rows}));
      }
    } catch(err){
      console.error(err);
    }
  });

  ws.on('close', () => {
    clients.delete(user);
  });
});
