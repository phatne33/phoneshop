import mysql.connector
import json
from transformers import AutoTokenizer, AutoModel
import torch
import sys
import numpy as np

# Kết nối cơ sở dữ liệu
def get_db_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="phonedb"
    )

# Tải mô hình Transformers
tokenizer = AutoTokenizer.from_pretrained("sentence-transformers/all-MiniLM-L6-v2")
model = AutoModel.from_pretrained("sentence-transformers/all-MiniLM-L6-v2")

# Hàm tạo embedding
def generate_embedding(text):
    inputs = tokenizer(text, return_tensors="pt", padding=True, truncation=True, max_length=512)
    with torch.no_grad():
        outputs = model(**inputs)
    return outputs.last_hidden_state.mean(dim=1).squeeze().numpy().tolist()

# Hàm tạo embedding cho tất cả sản phẩm
def create_product_embeddings():
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    
    cursor.execute("""
        SELECT p.product_id, p.product_name, p.description, p.specifications,
               c.category_name
        FROM tbl_products p
        LEFT JOIN tbl_categories c ON p.category_id = c.category_id
    """)
    
    products = cursor.fetchall()
    embeddings = []
    
    for product in products:
        text = f"{product['product_name']} {product['description']} {product['specifications']} {product['category_name']}"
        embedding = generate_embedding(text)
        embeddings.append({
            'product_id': product['product_id'],
            'embedding': embedding
        })
    
    with open('product_embeddings.json', 'w', encoding='utf-8') as f:
        json.dump(embeddings, f, ensure_ascii=False)
    
    cursor.close()
    conn.close()

# Hàm tạo embedding cho truy vấn
def get_query_embedding(query):
    return generate_embedding(query)

if __name__ == "__main__":
    if len(sys.argv) > 1:
        query = sys.argv[1]
        embedding = get_query_embedding(query)
        print(json.dumps(embedding))
    else:
        create_product_embeddings()
        print("Đã tạo embedding cho sản phẩm và lưu vào product_embeddings.json")