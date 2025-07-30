from sentence_transformers import SentenceTransformer
import numpy as np
from scipy.spatial.distance import cosine
import mysql.connector
import pandas as pd

# Kết nối cơ sở dữ liệu
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="phonedb"
)
cursor = db.cursor()

# Lấy dữ liệu sản phẩm
cursor.execute("SELECT product_id, product_name, description FROM tbl_products WHERE status = 'active'")
products = pd.DataFrame(cursor.fetchall(), columns=['product_id', 'product_name', 'description'])

# Tải mô hình embedding
model = SentenceTransformer('all-MiniLM-L6-v2')

# Tạo embedding cho mô tả sản phẩm
product_descriptions = products['description'].fillna('').tolist()
product_embeddings = model.encode(product_descriptions, convert_to_tensor=False)

# Hàm tìm sản phẩm phù hợp
def find_relevant_products(query, top_k=3):
    query_embedding = model.encode([query])[0]
    similarities = [1 - cosine(query_embedding, prod_emb) for prod_emb in product_embeddings]
    top_indices = np.argsort(similarities)[-top_k:][::-1]
    return products.iloc[top_indices][['product_id', 'product_name']].to_dict('records')

# Ví dụ sử dụng
query = "điện thoại chụp ảnh đẹp giá rẻ"
relevant_products = find_relevant_products(query)
print("Sản phẩm phù hợp:", relevant_products)

# Đóng kết nối
cursor.close()
db.close()