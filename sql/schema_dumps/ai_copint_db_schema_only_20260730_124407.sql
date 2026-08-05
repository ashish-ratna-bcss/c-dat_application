--
-- PostgreSQL database dump
--

\restrict LiYn9x7toVYvKdbKS1Is5LkOju4PG6hrElJDctEeqv0wD31wxAukjGXGi6y9A5U

-- Dumped from database version 16.14 (Ubuntu 16.14-1.pgdg24.04+1)
-- Dumped by pg_dump version 16.14 (Ubuntu 16.14-1.pgdg24.04+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.audit_logs (
    id character varying(36) NOT NULL,
    user_id character varying(36),
    action character varying(50) NOT NULL,
    status character varying(20) NOT NULL,
    ip_address character varying(45),
    details text,
    "timestamp" timestamp with time zone NOT NULL
);


--
-- Name: user_activity_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_activity_logs (
    id character varying(36) NOT NULL,
    user_id character varying(36),
    username character varying(50),
    action character varying(50) NOT NULL,
    module character varying(50),
    target character varying(255),
    details text,
    ip_address character varying(45),
    status character varying(20) NOT NULL,
    created_at timestamp with time zone NOT NULL
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id character varying(36) NOT NULL,
    full_name character varying(100) NOT NULL,
    username character varying(50) NOT NULL,
    password_hash character varying(255) NOT NULL,
    role character varying(20) NOT NULL,
    is_active boolean NOT NULL,
    created_by character varying(36),
    created_at timestamp with time zone NOT NULL,
    updated_at timestamp with time zone NOT NULL,
    last_login timestamp with time zone,
    failed_login_attempts integer NOT NULL,
    account_locked boolean NOT NULL
);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- Name: user_activity_logs user_activity_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_activity_logs
    ADD CONSTRAINT user_activity_logs_pkey PRIMARY KEY (id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: ix_audit_action; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_audit_action ON public.audit_logs USING btree (action);


--
-- Name: ix_audit_logs_timestamp; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_audit_logs_timestamp ON public.audit_logs USING btree ("timestamp");


--
-- Name: ix_audit_user_timestamp; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_audit_user_timestamp ON public.audit_logs USING btree (user_id, "timestamp");


--
-- Name: ix_ual_action; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_ual_action ON public.user_activity_logs USING btree (action);


--
-- Name: ix_ual_created; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_ual_created ON public.user_activity_logs USING btree (created_at);


--
-- Name: ix_ual_module; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_ual_module ON public.user_activity_logs USING btree (module);


--
-- Name: ix_ual_user_created; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_ual_user_created ON public.user_activity_logs USING btree (user_id, created_at);


--
-- Name: ix_user_activity_logs_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_user_activity_logs_user_id ON public.user_activity_logs USING btree (user_id);


--
-- Name: ix_users_role_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_users_role_active ON public.users USING btree (role, is_active);


--
-- Name: ix_users_username; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX ix_users_username ON public.users USING btree (username);


--
-- Name: audit_logs audit_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: user_activity_logs user_activity_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_activity_logs
    ADD CONSTRAINT user_activity_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: users users_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- PostgreSQL database dump complete
--

\unrestrict LiYn9x7toVYvKdbKS1Is5LkOju4PG6hrElJDctEeqv0wD31wxAukjGXGi6y9A5U

