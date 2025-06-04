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

function broadcast(msg, except){
  for(const [u,client] of clients){
    if(client!==except && client.readyState===WebSocket.OPEN){
      client.send(msg);
    }
  }
}

wss.on('connection', async (ws, req) => {
  const params = new URL(req.url, `http://${req.headers.host}`).searchParams;
  const user = params.get('user');
  if(!user){ ws.close(); return; }
  const [uRow] = await pool.query('SELECT id, role FROM users WHERE username=?', [user]);
  if(!uRow[0]) { ws.close(); return; }
  const userId = uRow[0].id;
  const userRole = uRow[0].role;
  clients.set(user, ws);
  broadcast(JSON.stringify({type:'presence', user, online:true}), ws);
  ws.send(JSON.stringify({type:'presence', users: Array.from(clients.keys())}));

  ws.on('message', async message => {
    try {
      const data = JSON.parse(message.toString());
      if(data.type === 'message') {
        const [rRow] = await pool.query('SELECT id FROM users WHERE username=?', [data.to]);
        if(!rRow[0]) return;
        const sId = userId;
        const rId = rRow[0].id;
        const [res] = await pool.query('INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)', [sId, rId, data.text]);
        const [row] = await pool.query('SELECT id, created_at FROM messages WHERE id=?', [res.insertId]);
        const msgRow = row[0];
        const payload = {type:'message', from:user, role:userRole, id:msgRow.id, text:data.text, time:msgRow.created_at, status:'sent'};
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
        const [rRow] = await pool.query('SELECT id FROM users WHERE username=?', [data.with]);
        if(!rRow[0]) return;
        const sId = userId;
        const rId = rRow[0].id;
        const [rows] = await pool.query('SELECT m.id, m.sender_id, m.message, m.created_at, m.is_read, u.role AS sender_role FROM messages m JOIN users u ON m.sender_id=u.id WHERE (m.sender_id=? AND m.receiver_id=?) OR (m.sender_id=? AND m.receiver_id=?) ORDER BY m.id', [sId, rId, rId, sId]);
        ws.send(JSON.stringify({type:'history', messages: rows}));
      }
    } catch(err){
      console.error(err);
    }
  });

  ws.on('close', () => {
    clients.delete(user);
    broadcast(JSON.stringify({type:'presence', user, online:false}));
  });
});
